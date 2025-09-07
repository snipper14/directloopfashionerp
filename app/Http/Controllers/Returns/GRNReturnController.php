<?php

namespace App\Http\Controllers\Returns;

use App\Branch\Branch;
use App\Returns\ReturnGRN;
use App\Suppliers\Supplier;
use App\Inventory\Inventory;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateQtyGRNReturns;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use Illuminate\Support\Facades\Validator;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class GRNReturnController extends BaseController
{
    function create(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'return_array' => 'required|array',
            'return_array.*.qty' => 'required|numeric|gt:0',

            'return_array.*.stock_id' => [new ValidateQtyGRNReturns($request)],
        ]);
        $return_code = $this->receiptNumberFinal();
        DB::transaction(function () use ($request, $return_code) {
            $sum_sub_total = 0;
            $total_vat = 0;
            for ($i = 0; $i < count($request->return_array); $i++) {
                $value = $request->return_array[$i];
                $sum_sub_total += $value['sub_total'];
                $total_vat += $value['tax_amount'];
                $insert_a = [
                    'stock_id' => $value['stock_id'],
                    'department_id' => $value['department_id'],
                    'purchase_order_receivable_id' => $value['purchase_order_receivable_id'],
                    'supplier_id' => $value['supplier_id'],
                    'unit_price' => $value['unit_price'],
                    'qty' => $value['qty'],
                    'tax_rate' => $value['tax_rate'],
                    'tax_amount' => $value['tax_amount'],
                    'sub_total' => $value['sub_total'],
                    'return_date' => $value['return_date'],
                    'returns_conditions' => $value['returns_conditions'],
                    'return_reasons' => $value['return_reasons'],
                    'return_code' => $return_code,
                    'delivery_no' => $value['delivery_no'],

                ] + Parent::user_branch_id();
                ReturnGRN::create($insert_a);

                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $value['department_id'], 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty -' . $value['qty'])]
                );
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(
                        ['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']]
                    )->update([
                        'qty_delivered' => DB::raw('qty_delivered -' . $value['qty'])
                    ]);
                }

                SupplierInvoice::where([
                    'po_no' => $value['order_no'],
                    'supplier_id' => $value['supplier_id'],

                ])->update(
                    [
                        'invoice_total' => DB::raw('invoice_total -' . $value['sub_total']),
                        'unpaid_amount' => DB::raw('unpaid_amount -' . $value['sub_total'])

                    ]
                );
            } //end loop
            $data2 = array(

                'supplier_id' => $request->return_array[0]['supplier_id'],
                'ref' => $return_code,
                'type' => 'RETURNS',
                'entry_date' => $request->return_array[0]['return_date'],
                'description' => "Return GRN",
                'debit' => $sum_sub_total,
                'amount_due' => 0,
                'balance' => 0
            );
            CreditLedger::create($data2 + Parent::user_branch_id());
            $this->creditVATAccount($total_vat, $request, $return_code);
            $this->creditStockLedgerAccount($sum_sub_total,$total_vat, $request, $return_code);
        }, 5);
    }
    function  creditStockLedgerAccount($sum_sub_total,$total_vat, $request, $return_code){
        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if ($accounts_data) {
            $value = $sum_sub_total - $total_vat;
            $supplier = Supplier::where('id', $request->return_array[0]['supplier_id'])->first();
            $insert_data = array(
                'other_account_name' => $supplier->company_name,
                'account_id' => $accounts_data->id,
                'account_type' => 'GRN Returns Inventory',
                'entry_code' => $return_code,
                'ref' => $return_code,
                'details' => " Purchase ( GRN) Returns",
                'credit_amount' => $value,
                'description' => "GRN Returns ",
                'entry_date' => Parent::currentDate(),
    
            );
    
            GeneralLedger::create($insert_data + Parent::user_branch_id());
        }
    }
    function creditVATAccount($total_vat, $request, $return_code)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "input VAT"])->first();
        if ($total_vat == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }
        $supplier = Supplier::where('id', $request->return_array[0]['supplier_id'])->first();

        $insert_data = array(
            'other_account_name' => $supplier->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'GRN RETURNS',
            'entry_code' => $return_code,
            'ref' => $return_code,
            'details' => "Returns From GRN",
            'credit_amount' => $total_vat,
            'description' => " VAT",
            'entry_date' => $request->return_array[0]['return_date'],

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function receiptNumberFinal()
    {
        $return_code =  '1';
        $res = ReturnGRN::orderBy('id', 'DESC')->first();
        if ($res) {
            $code = $res->return_code;
            $res2 = ReturnGRN::orderBy('id', 'ASC')->where('return_code', $code)->first();
            $id = $res2->id;
            $current_return_code = $id + 1;
            $return_code = '0' . $current_return_code;
        }

        return  $return_code;
    }
    function queryReport()
    {
        $main_query = ReturnGRN::with(['department', 'user', 'supplier', 'purchase_order_receivable', 'stock'])->orderBy('id', 'DESC');
        if (request('from') != "" && request('to') != "") {
            $main_query->whereBetween('return_date', [request('from'), request('to')]);
        }
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('return_code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                    })->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });
        $main_query->when(request('supplier_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('supplier_id',  request('supplier_id'));
            });
        });
        return $main_query;
    }
    function fetchItemized(){
        $main_query = $this->queryReport()->latest();
       
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data =  $main_query->paginate(30);
        }
        return $data;
    }
    function fetchGrouped()
    {
        $main_query = $this->queryReport();
        $main_query->selectRaw("*,SUM(qty) AS total_qty,SUM(sub_total) AS total_sub_total,SUM(tax_amount) AS sum_tax_amount")->groupBy('return_code')->latest();
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data =  $main_query->paginate(30);
        }
        return $data;
    }
    function fetchTotal()
    {
        $main_query = $this->queryReport();
        $main_query->selectRaw("SUM(qty) AS total_qty,SUM(sub_total) AS total_sub_total,SUM(tax_amount) AS sum_tax_amount");
        $data =  $main_query->first();

        return $data;
    }

    function fetchReturnDetails()
    {
        $res =  ReturnGRN::with(['stock', 'department', 'user', 'supplier'])->where([

            'return_code' => request('return_code'),

        ])->get();
        return response()->json($res);
    }
    function downloadDN(Request $request)
    {

        ini_set('max_execution_time', 2400);

        $supplier_id = $request->supplier_id;
        $order_no = $request->order_no;


        $branch = Branch::where('id', Auth::user()->branch_id)->first();
        $res =  ReturnGRN::with(['department', 'user', 'supplier', 'purchase_order_receivable', 'stock'])->where([
            'return_code' => $request->return_code,

        ])->get();



        // return response()->json($data);
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "return_grn" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, 'branch' => $branch];
        $pdf = PDF::loadView('pdf.grn_return', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }
}
