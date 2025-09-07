<?php

namespace App\Http\Controllers\Sale;

use App\Sale\Sale;
use App\Cart\OrderCart;
use App\Customer\Customer;
use App\Employee\Employee;
use App\Sale\TakeAwaySale;
use Illuminate\Http\Request;
use App\Sale\TakeAwaySoldItem;
use App\TakeAway\TakeawayOrder;
use App\Transaction\Transaction;
use Illuminate\Support\Facades\DB;
use App\POS\OrderEmployeeDeduction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Sale\TakeAwaySaleRequest;

class TakeAwaySaleController extends BaseController
{
    public function create(TakeAwaySaleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $res = TakeawayOrder::where(['order_no' => $request->order_no])->get();
            $res_takeaway_sale = TakeAwaySale::create($request->validated() +
                ['cashier_id' => Parent::user_id()] +
                Parent::branch_array());

            $customer_name = Customer::where('id', $request->customer_id)->first()->company_name;
            $insert_ledger_data = array(


                'ref' => $request->receipt_no,
                'entry_date' => $request->paid_date,
                'description' => 'Take Away Sales',
                'credit' => "Sales",
                'debit' => $customer_name,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    TakeAwaySoldItem::create(
                        [
                            'order_no' => $value['order_no'],
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'department_id' => $value['department_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id(),
                            'take_away_sale_id' => $res_takeaway_sale->id,
                            'order_date' => $value['order_date'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'vat_rate' => $value['vat_rate'],
                            'row_vat' => $value['row_vat'],

                            'order_type' => $value['order_type'],

                        ]

                    );
                }
            }


            TakeawayOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }


    public function salesReport()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = TakeAwaySale::with([
            'customer', 'user', 'cashier',
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('paid_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array())->latest('created_at');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }


    public function salesReportTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = TakeAwaySale::with([
            'customer', 'user', 'cashier',
        ]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('paid_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array())->selectRaw('*,
        SUM(receipt_total) AS sum_receipt_total,
        SUM(cash_pay+receipt_balance) AS sum_cash_pay,
        SUM(mpesa_pay) AS sum_mpesa_pay,
        SUM(card_pay) AS sum_card_pay,
        SUM(credit_pay) AS sum_credit_pay,
        SUM(total_vat) AS sum_total_vat
        ');
        $res = $main_query->first();

        return response()->json($res);
    }


    function getSaleDetails()
    {
        $order_no = request('order_no');
        $res = TakeAwaySoldItem::with([
            'stock', 'unit', 'customer', 'user'
        ])->where('order_no', $order_no)->get();

        return response()->json($res);
    }


    public function detailsReport()
    {
        ini_set('max_execution_time', 2400);
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = TakeAwaySoldItem::with([
            'customer', 'user', 'unit', 'stock'
        ]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });

        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array());
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(100);
        }
        return response()->json($res);
    }



    public function  soldItemTotals()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = TakeAwaySoldItem::with([
            'customer', 'user', 'unit', 'stock'
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }

        $main_query->where(Parent::branch_array())->selectRaw('*,
        SUM(row_total) AS sum_row_total,
        SUM(row_vat) AS sum_row_vat,
        SUM(cost_total) AS total_cost
       ');
        $res = $main_query->first();

        return response()->json($res);
    }




    function completeCreditSale(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res = TakeawayOrder::where(['order_no' => $request->order_no])->get();

            $customer_name = Customer::where('id', $request->customer_id)->first()->company_name;
            $insert_ledger_data = array(


                'ref' => $request->receipt_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'credit sales',
                'credit' => "Stock",
                'debit' => $customer_name,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {

                    OrderCart::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'branch_id' => $value['branch_id'],

                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'selling_price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'tax_amount' => $value['row_vat'],
                            'order_no' => $value['order_no'],

                        ]

                    );
                    Sale::create([
                        "stock_id" => $value['stock_id'],
                        'customer_id' => $value['customer_id'],
                        'branch_id' => $value['branch_id'],
                        'user_id' => $value['user_id'],
                        'order_date' => $value['order_date'],
                        'order_no' => $value['order_no'],
                        'qty' => $value['qty'],
                        'item_price' => $value['price'],
                        'row_total' => $value['row_total'],
                        'order_total' => $request->receipt_total,
                        'tax_amount' => $value['row_vat'],

                    ] + Parent::user_branch_id());

                    TakeAwaySoldItem::create(
                        [
                            'order_no' => $value['order_no'],
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id(),
                            'department_id' =>  $value['department_id'],

                            'order_date' => $value['order_date'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'vat_rate' => $value['vat_rate'],
                            'row_vat' => $value['row_vat'],

                            'order_type' => $value['order_type'],

                        ]
                    );
                }
            }


            TakeawayOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }


    function completeOrderByEmployeeDeduction(Request $request)
    {
        $request->validate([
            "employee_id" => "required"
        ]);
        DB::transaction(function () use ($request) {
            $res = TakeawayOrder::where(['order_no' => $request->order_no])->get();

            $emp_data = Employee::where('id', $request->employee_id)->first();
            $insert_ledger_data = array(


                'ref' => $request->receipt_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'salary deduction',
                'credit' => "Stock",
                'debit' => $emp_data->first_name . ' ' . $emp_data->last_name,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {

                    OrderEmployeeDeduction::create(
                        [
                            'branch_id' => $value['branch_id'],
                            'user_id' => $value['user_id'],
                            'employee_id' => $request->employee_id,
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_date' => $value['order_date'],
                            'order_no' => $value['order_no'],
                            'order_type' => $value['order_type'],
                            'cashier_id' => Parent::user_id()
                        ]

                    );

                    TakeAwaySoldItem::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'department_id' => $value['department_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),

                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],
                            'prev_order_no' => $value['prev_order_no'],
                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                        ]
                            + Parent::branch_array()
                    );
                }
            }


            TakeawayOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }
}
