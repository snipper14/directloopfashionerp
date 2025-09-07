<?php

namespace App\Http\Controllers\Credit;

use App\Stock\Stock;
use App\POS\POSCredit;
use App\dept\Department;
use App\Customer\Customer;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Rules\ValidateBatchNo;
use App\Rules\ValidateBatchQty;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidateExpiryOnSale;
use App\Rules\ValidatePOSCreditQty;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateStockAvailability;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\POSCredit\POSCreditRequest;
use App\Http\Requests\Refund\RetailsSaleRefundRequest;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;
use App\Refund\RetailSalesRefund;

class POSCreditController extends BaseController
{
    public function create(POSCreditRequest $request)
    {
        $this->validate($request, ['qty' => new ValidatePOSCreditQty($request->all_sale_details_report_id, $request->receipt_qty)]);

        POSCredit::updateOrCreate([
            'stock_id' => $request->stock_id,
            'credit_no' => $request->credit_no
        ], $request->validated() + Parent::user_branch_id());
        $res = POSCredit::where(['credit_no' => $request->credit_no])->with(['stock'])->get();

        return response()->json($res);
    }

    public function fetchPendingCr(Request $request)
    {
        $res =   POSCredit::where(['receipt_no' => $request->receipt_no, 'status' => 'pending'])->with(['stock'])->get();
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        POSCredit::where(['id' => $request->route('id')])->delete();
        return true;
    }

    public function deleteCCompletedCr(Request $request)
    {
        DB::transaction(function () use ($request) {
            $id = $request->route('id');
            $res = POSCredit::with(['all_sale_details_report'])->where(['id' => $id])->first();
            if ($res->deduct_stock) {
                echo ($res->all_sale_details_report->department_id);
                if (Parent::isInventory($res->stock_id)) {
                    Inventory::where([
                        'stock_id' => $res->stock_id,
                        'department_id' => $res->all_sale_details_report->department_id
                    ])->decrement('qty', $res->qty);
                }
            }
            POSCredit::where(['id' => $id])->delete();
        }, 5);
        return true;
    }
    public function completeCr(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
        ]);
        DB::transaction(function () use ($request) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            Inventory::where([
                                'branch_id' => Parent::branch_id(),
                                'stock_id' => $value['stock_id'],
                                'department_id' => $value['all_sale_details_report']['department_id']
                            ])->increment('qty', $value['qty']);
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'receipt_total' => DB::raw('receipt_total -' . $value['row_total']),
                        'total_vat' => DB::raw('total_vat -' . $value['row_vat'])
                    ]);
                }
            }
            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed', 'cu_invoice_no' => $request->cu_invoice_no]);
        }, 5);
        return true;
    }

    public function fetch()
{
    $from = request('from') ? date(request('from')) : null;
    $to = request('to') ? date(request('to')) : null;

    $main_query = POSCredit::query()
        ->with(['stock', 'user', 'all_sale_details_report.stock'])
        ->leftJoin('users', 'users.id', '=', 'p_o_s_credits.user_id')
        ->select('p_o_s_credits.*', 'users.name as user_name')
        ->selectRaw('SUM(credit_vat) AS total_vat, SUM(credit_amount) AS total_amount')
        ->groupBy('credit_no', 'users.name');

    // Date filter
    if ($from && $to) {
        $main_query->whereBetween('credit_date', [$from, $to]);
    }

    // User filter
    if (request('user_id')) {
        $main_query->where('user_id', request('user_id'));
    }

    // Search filter
    if (request('search')) {
        $main_query->where(function ($q) {
            $q->where('credit_no', 'LIKE', '%' . request('search') . '%')
              ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
              ->orWhere('etims_customer_name', 'LIKE', '%' . request('search') . '%')
              ->orWhere('etims_customer_pin', 'LIKE', '%' . request('search') . '%')
              ->orWhere('users.name', 'LIKE', '%' . request('search') . '%');
        });
    }

    // Specific credit_no bypasses aggregation
    if (request('credit_no')) {
        $main_query->where([
            'credit_no' => request('credit_no'),
            'status' => 'completed',
        ]);
    } else {
        // Sorting logic
        if (in_array(strtoupper(request('sort_total_amount')), ['ASC', 'DESC'])) {
            $main_query->orderByRaw('SUM(credit_amount) ' . request('sort_total_amount'));
        } elseif (in_array(strtoupper(request('sort_total_vat')), ['ASC', 'DESC'])) {
            $main_query->orderByRaw('SUM(credit_vat) ' . request('sort_total_vat'));
        } elseif (in_array(strtoupper(request('sort_receipt_no')), ['ASC', 'DESC'])) {
            $main_query->orderBy('receipt_no', request('sort_receipt_no'));
        } elseif (in_array(strtoupper(request('sort_user_name')), ['ASC', 'DESC'])) {
            $main_query->orderBy('users.name', request('sort_user_name'));
        }
        elseif (in_array(strtoupper(request('sort_created_at')), ['ASC', 'DESC'])) {
            $main_query->orderBy('p_o_s_credits.created_at', request('sort_created_at'));
        } else {
            $main_query->orderByDesc('p_o_s_credits.created_at');
        }
    }

    // Pagination
    $res = request('page') > 0 ? $main_query->paginate(30) : $main_query->get();

    return response()->json($res);
}

    function fetchTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = POSCredit::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('credit_date', [$from, $to]);
        });

        $main_query->when(request('user_id', '') != '', function ($query) {
            $query->where('user_id', request('user_id'));
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('credit_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('etims_customer_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('etims_customer_pin', 'LIKE', '%' . request('search') . '%')


                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        $main_query->selectRaw('
        SUM(credit_vat) AS total_vat,
        SUM(credit_amount) AS total_amount ');


        $res = $main_query->first();

        return response()->json($res);
    }


    public function exchangeWithOriginalReceipt(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],

        ]);
        DB::transaction(function () use ($request) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            $receipt_no = null;
            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    $receipt_no = $value['receipt_no'];
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            Inventory::where([
                                'branch_id' => Parent::branch_id(),
                                'stock_id' => $value['stock_id'],
                                'department_id' => $value['all_sale_details_report']['department_id']
                            ])->increment('qty', $value['qty']);
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no'], 'stock_id' => $value['stock_id']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([

                        'total_vat' => DB::raw('total_vat -' . $value['row_vat'])
                    ]);

                    if ($value['all_sale_details_report']['batch_no']) {
                        PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['all_sale_details_report']['batch_no']])->decrement('qty_sold', $value['qty']);
                    }
                }
            }
            $department_id  =   Department::where('show_pos', 1)->first()->id;

            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                $customer = Customer::where('id', $value['customer_id'])->first();
                $customer_name = $customer ? $customer->company_name : "";
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $customer_name,
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );
                AllSalesTotalReport::where(['receipt_no' => $receipt_no])->update([

                    'total_vat' => DB::raw('total_vat +' . $value['row_vat'])
                ]);
                if (Parent::isInventory($value['stock_id'])) {

                    Inventory::where([
                        'branch_id' => Parent::branch_id(),
                        'stock_id' => $value['stock_id'],
                        'department_id' => $department_id
                    ])->decrement('qty', $value['qty']);
                }
            }
            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed', 'cu_invoice_no' => $request->cu_invoice_no]);
        }, 5);
        return true;
    }
    public function exchangeWithPartialOriginalReceipt(Request $request)
    {
        if (count($request->order_data_offline) > 0) {
            $validator = Validator::validate($request->all(), [
                'order_data_offline' => 'required|array',
                'order_data_offline.*.qty' => 'required|numeric|gt:0',
                'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],

            ]);
        }
        DB::transaction(function () use ($request) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            $receipt_no = null;
            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    $receipt_no = $value['receipt_no'];
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            Inventory::where([
                                'branch_id' => Parent::branch_id(),
                                'stock_id' => $value['stock_id'],
                                'department_id' => $value['all_sale_details_report']['department_id']
                            ])->increment('qty', $value['qty']);
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no'], 'stock_id' => $value['stock_id']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([

                        'total_vat' => DB::raw('total_vat -' . $value['row_vat'])
                    ]);

                    if ($value['all_sale_details_report']['batch_no']) {
                        PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['all_sale_details_report']['batch_no']])->decrement('qty_sold', $value['qty']);
                    }
                }
            }
            $department_id  =   Department::where('show_pos', 1)->first()->id;

            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                $customer = Customer::where('id', $value['customer_id'])->first();
                $customer_name = $customer ? $customer->company_name : "";
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $customer_name,
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );
                AllSalesTotalReport::where(['receipt_no' => $receipt_no])->update([

                    'total_vat' => DB::raw('total_vat +' . $value['row_vat'])
                ]);
                if (Parent::isInventory($value['stock_id'])) {

                    Inventory::where([
                        'branch_id' => Parent::branch_id(),
                        'stock_id' => $value['stock_id'],
                        'department_id' => $department_id
                    ])->decrement('qty', $value['qty']);
                }
            }
            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed', 'cu_invoice_no' => $request->cu_invoice_no]);
        }, 5);
        return true;
    }
    public function commitRefund(RetailsSaleRefundRequest $request)
    {
        DB::transaction(function () use ($request) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            $receipt_no = null;
            if (!$res->isEmpty()) {
                foreach ($res as $value) {

                    $receipt_no = $value['receipt_no'];
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            Inventory::where([
                                'branch_id' => Parent::branch_id(),
                                'stock_id' => $value['stock_id'],
                                'department_id' => $value['all_sale_details_report']['department_id']
                            ])->increment('qty', $value['qty']);
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no'], 'stock_id' => $value['stock_id']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([

                        'total_vat' => DB::raw('total_vat -' . $value['row_vat']),
                        'receipt_total' => DB::raw('receipt_total -' . $request->refund_amount)
                    ]);

                    if ($value['all_sale_details_report']['batch_no']) {
                        PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['all_sale_details_report']['batch_no']])->decrement('qty_sold', $value['qty']);
                    }
                }
                $insert_data = array(
                    'other_account_name' => "Retail Sales Returns",
                    'account_id' => $request->account_id,
                    'account_type' => 'retail',
                    'entry_code' => $request->ref_details,
                    'ref' => $receipt_no,
                    'details' => "Refund",
                    'credit_amount' => $request->refund_amount,
                    'description' => "Refund",
                    'entry_date' => $request->credit_date,

                );
                GeneralLedger::create($insert_data + Parent::user_branch_id());
                RetailSalesRefund::create($request->validated() + Parent::user_branch_id());
            }

            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed']);
        }, 5);
        return true;
    }
   public function fetchRefunds()
{
    $from = request('from') ? date(request('from')) : null;
    $to = request('to') ? date(request('to')) : null;

    $main_query = RetailSalesRefund::query()
        ->with(['user', 'branch', 'account'])
        ->leftJoin('users', 'users.id', '=', 'retail_sales_refunds.user_id')
        ->leftJoin('ledger_accounts', 'ledger_accounts.id', '=', 'retail_sales_refunds.account_id')
        ->select('retail_sales_refunds.*', 'users.name as user_name', 'ledger_accounts.account as account_name');

    // Filter by date
    if ($from && $to) {
        $main_query->whereBetween('credit_date', [$from, $to]);
    }

    // Filter by user
    if (request('user_id')) {
        $main_query->where('retail_sales_refunds.user_id', request('user_id'));
    }

    // Search filter
    if (request('search')) {
        $main_query->where(function ($q) {
            $q->where('credit_no', 'LIKE', '%' . request('search') . '%')
              ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
              ->orWhere('users.name', 'LIKE', '%' . request('search') . '%')
              ->orWhere('ledger_accounts.account', 'LIKE', '%' . request('search') . '%');
        });
    }

    // Sorting
    if (in_array(strtoupper(request('sort_refund_amount')), ['ASC', 'DESC'])) {
        $main_query->orderBy('refund_amount', request('sort_refund_amount'));
    }

    if (in_array(strtoupper(request('sort_created_at')), ['ASC', 'DESC'])) {
        $main_query->orderBy('retail_sales_refunds.created_at', request('sort_created_at'));
    }

    if (in_array(strtoupper(request('sort_user')), ['ASC', 'DESC'])) {
        $main_query->orderBy('users.name', request('sort_user'));
    }

    if (in_array(strtoupper(request('sort_account_name')), ['ASC', 'DESC'])) {
        $main_query->orderBy('ledger_accounts.account', request('sort_account_name'));
    }

    // Default latest fallback if no sort applied
    if (
        !request('sort_refund_amount') &&
        !request('sort_created_at') &&
        !request('sort_user') &&
        !request('sort_account_name')
    ) {
        $main_query->latest('retail_sales_refunds.created_at');
    }

    // Pagination
    $res = request('page') > 0 ? $main_query->paginate(40) : $main_query->get();

    return response()->json($res);
}

    function fetchRefundsTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RetailSalesRefund::with(['user', 'branch', 'account'])->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('credit_date', [$from, $to]);
        });

        $main_query->when(request('user_id', '') != '', function ($query) {
            $query->where('user_id', request('user_id'));
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('credit_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')


                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('account', function ($q) {
                    $q->where('account', 'LIKE', '%' . request('search') . '%');
                });
        });
        $main_query->selectRaw('
       
        SUM(refund_amount) AS total_amount ');


        $res = $main_query->first();

        return response()->json($res);
        return response()->json($res);
    }
}
