<?php

namespace App\Http\Controllers\SupplierPayment;

use App\Invoices\Invoice;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Transaction\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use App\SupplierPayment\SupplierPayment;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;
use App\Http\Requests\SupplierPayment\SupplierPaymentRequest;

class SupplierPaymentController extends BaseController
{
    public function create(SupplierPaymentRequest $request)
    {
        Validator::validate($request->all(), [
          
            'supplier_id' =>[
                
                new ValidateRegisterActiveSales($request)
            ],
          
           
        ]);
        DB::transaction(function () use ($request) {
            $invoice_data = $request->invoice_data;
            SupplierPayment::create($request->validated() + Parent::user_branch_id());
            $this->deductCreditLedger($request);

            $this->debitGeneralLedger($request);
            foreach ($invoice_data as $value) {
                SupplierInvoice::where(['invoice_no' => $value['invoice_no']])->update(
                    ['paid_amount' => DB::raw('paid_amount +' .  $value['amount_paid'])] +
                        ['unpaid_amount' => DB::raw('unpaid_amount -' .  $value['amount_paid'])]
                );
            }
        }, 5);
        return true;
    }

    function debitGeneralLedger(Request $request)
    {
        $cust = Supplier::where('id', $request->supplier_id)->first();

        $date = $request->date_paid;
        $insert_data = array(
            'other_account_name' => $cust->company_name,
            'account_id' => $request->account_id,
            'account_type' => 'supplier',
            'entry_code' => $request->ref_no,
            'ref' => $request->ref_no,
            'details' => "Payment",
            'credit_amount' => $request->amount_paid,
            'description' => $request->description,
            'entry_date' => $date,

        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    public function deductCreditLedger(SupplierPaymentRequest $request)
    {
        $res =  CreditLedger::where(['supplier_id' => $request->supplier_id])->orderBy('created_at', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $credit_amount =  $request->amount_paid;
        $total_balance = $balance - $credit_amount;

        $data = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->ref_no,
            'type' => 'Payment',
            'entry_date' => $request->date_paid,
            'description' => 'Payments',
            'debit' => $credit_amount,
            'amount_due' => $request->outstanding_balance,
            'balance' => $total_balance
        );
        CreditLedger::create($data + Parent::user_branch_id());

        $supp = Supplier::where('id', $request->supplier_id)->first();
        $insert_ledger_data = array(

            'ref' => $request->ref_no,

            'entry_date' => $request->date_paid,
            'description' => "Supplier payment",
            'debit' => $supp->company_name,
            'credit' => $request->pay_method,
            'amount' => $credit_amount,
        );
        Transaction::create($insert_ledger_data + Parent::user_branch_id());
    }
    public function fetch_details(Request $request)
    {
        $id = $request->route('id');
        $data = SupplierPayment::where('supplier_id', $id)->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $data]);
    }
    public function fetchInvoiceSupplierPaymentReport()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = SupplierPayment::withoutGlobalScope(BranchScope::class)->where('supplier_payments.branch_id',Parent::branch_id())->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_paid', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('supplier_invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('po_no', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['supplier', 'user', 'supplier_invoice'])


            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('supplier_payments.date_paid', [$from, $to]);
                }
            )  ->when(request('sort_supplier'), function ($q) {
                $q->join('suppliers', 'suppliers.id', '=', 'supplier_payments.supplier_id')
                    ->orderBy('suppliers.company_name', request('sort_supplier'));
            })->when(request('sort_amount_paid'),function($q){
                $q->orderBy('supplier_payments.amount_paid', request('sort_amount_paid'));
            })
            ->when(request('sort_outstanding_balance'),function($q){
                $q->orderBy('supplier_payments.outstanding_balance', request('sort_outstanding_balance'));
            })
            
            ->orderBy('supplier_payments.id', 'ASC');
        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(20);
            return response()->json($data);
        }

        return response()->json($data);
    }

    public function fetchInvoiceSupplierPaymentTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = SupplierPayment::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_paid', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('supplier_invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('po_no', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->selectRaw('SUM(amount_paid) AS total_amount_paid')

            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('date_paid', [$from, $to]);
                }
            );

        $data = $main_query->first();

        return response()->json($data);
    }
    public function fetchSupplierPaymentReport()
    {
        $data = SupplierPayment::withoutGlobalScope(BranchScope::class)->where('supplier_payments.branch_id',Parent::branch_id())
        ->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_paid', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['supplier', 'user'])
        ->when(request('sort_supplier'), function ($q) {
            $q->join('suppliers', 'suppliers.id', '=', 'supplier_payments.supplier_id')
                ->orderBy('suppliers.company_name', request('sort_supplier'));
        })
        ->when(request('sort_total_amount_paid'), function ($q) {
            $q->orderBy('total_amount_paid', request('sort_total_amount_paid'));
        })
            ->selectRaw('supplier_payments.*,SUM(supplier_payments.amount_paid) AS total_amount_paid')
            ->groupBy('supplier_payments.supplier_id')
            
            ->orderBy('id', 'DESC')->paginate(20);

        return response()->json($data);
    }


    public function exportExcel()
    {
        $data = SupplierPayment::with(['supplier', 'user'])
            ->where(Parent::branch_array())
            ->orderBy('id', 'DESC')->get();
        return response()->json($data);
    }
}
