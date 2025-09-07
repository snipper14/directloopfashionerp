<?php

namespace App\Http\Controllers\CustomerPayment;

use App\Invoices\Invoice;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Transaction\Transaction;
use App\Payments\CustomerPayment;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;
use App\Http\Requests\Payment\CustomerPaymentRequest;

class CustomerPaymentController extends BaseController
{
    public function create(CustomerPaymentRequest $request)
    {
        Validator::validate($request->all(), [
          
            'customer_id' =>[
                
                new ValidateRegisterActiveSales($request)
            ],
          
           
        ]);
        DB::transaction(function () use ($request) {
            $invoice_data = $request->invoice_data;
            $amount_due = $request->amount_pending - $request->amount_paid;
            $result = CustomerPayment::create($request->validated() + ['amount_due' => $amount_due] + Parent::user_branch_id());
            $this->debitCustLedger($request, $request->ref_no, $request->amount_paid, $amount_due);
            if ($request->pay_method != "Cash") {
                $this->creditGeneralLedger($request);
            }

            foreach ($invoice_data as $value) {
                Invoice::where(['invoice_no' => $value['invoice_no']])->update(
                    ['amount_paid' => DB::raw('amount_paid +' .  $value['amount_paid'])] +
                        ['unpaid_amount' => DB::raw('unpaid_amount -' .  $value['amount_paid'])]
                );
            }
        }, 5);
        return true;
    }

    function creditGeneralLedger(Request $request)
    {
        $cust = Customer::where('id', $request->customer_id)->first();
        $details = $request->payment_details ? '/' . $request->payment_details : '';
        $date = $request->date_paid;
        $insert_data = array(
            'other_account_name' => $cust->company_name,
            'account_id' => $request->account_id,
            'account_type' => 'customer',
            'entry_code' => $request->ref_no,
            'ref' => $request->ref_no,
            'details' => "Payment",
            'debit_amount' => $request->amount_paid,
            'description' => $request->description . $details,
            'entry_date' => $date,

        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }

    function debitCustLedger(Request $request, $ref_no, $total_debit, $amount_due)
    {
        $res =  CustomerLedger::where(['customer_id' => $request->customer_id])->orderBy('created_at', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_balance = $balance - $total_debit;
        $date = $request->date_paid;
        $details = $request->payment_details ? '/' . $request->payment_details : '';
        $insert_data = array(

            'customer_id' => $request->customer_id,
            'ref' => $ref_no,
            'type' => 'PY',
            'entry_date' => $date,
            'description' => $request->description . $details,
            'credit' => $total_debit,
            'amount_due' => $amount_due,
            'balance' => $total_balance
        );
        CustomerLedger::create($insert_data + Parent::user_branch_id());

        $cust = Customer::where('id', $request->customer_id)->first();
        $insert_ledger_data = array(

            'ref' => $ref_no,

            'entry_date' => $date,
            'description' => "Customer Payment",
            'credit' => $cust->company_name,
            'debit' => $request->pay_method,
            'amount' => $total_debit,
        );
        Transaction::create($insert_ledger_data + Parent::user_branch_id());
    }


    public function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $row_query = CustomerPayment::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_paid', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('date_paid', [$from, $to]);
                }
            )
            ->with(['customer', 'user', 'invoice'])->where(Parent::branch_array())
            ->when(request('sort_amount_paid'), function ($query) {
                $query->orderBy('amount_paid', request('sort_amount_paid'));
            })->orderBy('id', 'ASC');
        $data = $row_query->get();
        if (request('page') > 0) {
            $data = $row_query->paginate(20);
            return response()->json($data);
        }

        return response()->json($data);
    }
    public function fetchTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $row_query = CustomerPayment::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_paid', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('date_paid', [$from, $to]);
                }
            );
        $row_query->selectRaw('
        SUM(amount_paid) AS sum_amount_paid
       
        ');
        $res = $row_query->first();

        return $res;
    }


    function destroy(Request $request)
    {
        $id = $request->route('id');
        CustomerPayment::where('id', $id)->delete();
        return true;
    }
    function queryAmountOwed(Request $request)
    {
        $customer_id = $request->customer_id;
        $res = CustomerLedger::where('customer_id', $customer_id)->latest('created_at')->first();

        $pending_amount = 0;
        if ($res) {
            $pending_amount = $res->balance;
        }
        return $pending_amount;
    }
    public function getAmountOwed(Request $request)
    {

        $pending_amount = $this->queryAmountOwed($request);


        return response()->json(["result" => $pending_amount]);
    }
    function getInvoiceAmountOwed(Request $request)
    {
        $customer_id = $request->customer_id;
        $res = CustomerPayment::where([
            'customer_id' => $customer_id,
            "invoice_id" => $request->invoice_id
        ])->latest('created_at')->get();
        $pending_amount = $this->queryAmountOwed($request);
        $paid_amount = 0;

        if (!$res->isEmpty()) {
            foreach ($res as  $value) {
                $paid_amount += $value['amount_paid'];
            }
        }


        return response()->json(["paid_amount" => $paid_amount, 'pending_amount' => $pending_amount]);
    }

    public function fetchCustomerBalances()
    {
        $customer_results = Customer::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('company_name', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        //  $customer_results = Customer::orderBy('id', 'DESC')->get();
        $result_array = array();
        foreach ($customer_results as $value) {
            $customer_id = $value['id'];
            $invoice_result = Invoice::where('customer_id', $customer_id)->selectRaw('sum(row_total) as total')->first();
            $invoice_total = $invoice_result ? $invoice_result->total : 0;

            $payment_result = CustomerPayment::where('customer_id', $customer_id)->where(Parent::branch_array())->selectRaw("date_paid,sum(amount_paid) as total_amount_paid")->orderBy('id', 'DESC')->first();
            $total_amount_paid = $payment_result ? $payment_result->total_amount_paid : 0;

            $pending_balance = $invoice_total - $total_amount_paid;

            array_push($result_array, [
                'invoice_total' => $invoice_total,
                'total_amount_paid' => $total_amount_paid,
                'pending_balance' => $pending_balance,
                'customer' => $value['company_name'],
                'customer_id' => $value['id'],
            ]);
        }

        return response()->json(['results' => $result_array]);
    }

    public function fetchCustomerStatement(Request $request)
    {
        $id = $request->customer_id;

        if ($request->from && $request->to) {
            $from = date($request->from);
            $to = date($request->to);

            $result =  CustomerPayment::with(['customer'])->where(['customer_id' => $id] + Parent::branch_array())->whereBetween('date_paid', [$from, $to])->get();
            return response()->json(['results' => $result]);
        }
        $result = CustomerPayment::with(['customer'])->where(['customer_id' => $id] + Parent::branch_array())->get();
        return response()->json(['results' => $result]);
    }

    public function downloadStatement(Request $request)
    {
        $data = $request->statement_data;
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' .  'customer_statement.pdf';
        $pdf = PDF::loadView('pdf.customer_statement', compact('data'));

        $pdf->save($path);

        return response()->download($path);
    }
}
