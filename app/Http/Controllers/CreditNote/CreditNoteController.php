<?php

namespace App\Http\Controllers\CreditNote;

use Carbon\Carbon;
use App\Invoices\Invoice;
use App\Customer\Customer;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\CreditNote\CreditNote;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Transaction\Transaction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateItemQtyDelivery;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreditNote\CreditNoteRequest;

class CreditNoteController extends BaseController
{
    public function create(CreditNoteRequest $request)
    {
        $qty = $request->qty;
        $invoice_id = $request->invoice_id;
        $this->validate($request, [
            'invoice_id' => new ValidateItemQtyDelivery($qty),

        ]);
        CreditNote::create($request->validated() +
            ['credit_no' => $this->generateCreditNo($request->invoice_no)] +
            Parent::user_branch_id());
        return true;
    }

    public function fetch()
    {
        $query = CreditNote::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('credit_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('invoice_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('invoice.customer', function ($query) {

                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['user',  'invoice.customer', 'invoice.stock'])->groupBy('credit_no')
        ->selectRaw('*,sum(line_total) as credit_total,sum(qty) as credit_qty')
        ->when(request('sort_total'), function ($query) {
            $query->orderBy('credit_total', request('sort_total'));
        })
        ->orderBy('id', 'DESC')
           ;
        $data = $query->get();
        if (request('page') > 0) {
            $data = $query->paginate(30);
        }
        return response()->json(['results' => $data]);
    }
    public function creditItems(Request $request)
    {
        $invoice_no = $request->route('invoice_no');
        $data = CreditNote::with(['invoice.stock'])
            ->where(['invoice_no' => $invoice_no, 'approved' => 0] + Parent::branch_array())
            ->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $data]);
    }
    public  function  getByInvoiceNo(Request $request)
    {
        $invoice_no = $request->route('invoice_no');
        $data = Invoice::with(['stock', 'customer'])
            ->where(['invoice_no' => $invoice_no] + Parent::branch_array())
            ->orderBy('id', 'DESC')->get();

        return response()->json(['results' => $data]);
    }


    public function destroy(Request $request)
    {
        $id = $request->route('id');
        CreditNote::find($id)->delete();
        return true;
    }

    public function approve(Request $request)
    {
        DB::transaction(function () use ($request) {
            $credit_no = $request->credit_no;
            $invoice_no = $request->invoice_no;
            CreditNote::where("credit_no", $credit_no)->update(['approved' => 1]);
            $results = CreditNote::with(['invoice'])->where('credit_no', $credit_no)->get();
           
                foreach ($results as  $value) {
                    $data_model = Invoice::find($value['invoice_id']);
                    $data_model->qty -= $value['qty'];
                    $data_model->tax_amount -= $value['row_vat'];

                    $data_model->row_total -= ($value['price'] * $value['qty']);
                    $data_model->save();
                    if ($value['reduce_stock']) {
                        if (Parent::isInventory($value['invoice']['stock_id'])) {
                            Inventory::where([
                                'department_id' => $value['department_id'],
                                'stock_id' => $value['invoice']['stock_id'],

                            ])->increment('qty', $value['qty']);
                        }
                    }
                }
           
            Invoice::where(['invoice_no' => $request->invoice_no])
                ->update(['unpaid_amount' => DB::raw('unpaid_amount -' .  $request->credit_total)]);
            $this->creditCustLedger($credit_no);
            $this->reduceOutputVATAccount($credit_no);
            
                $this->debitInventoryAccount( $credit_no);
                
        }, 5);
        return true;
    }

    function debitInventoryAccount($credit_no)
    {

        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if ($accounts_data) {
            $results = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->get();
            $total_vat = 0;
            $total_debit = 0;
            if (count($results)) {
                foreach ($results as  $value) {
                    if ($value['reduce_stock']) {
                        $total_debit += $value['line_total'];
                        $total_vat += $value['row_vat'];
                    }
                }
            }
            if ($total_debit > 0) {
                $insert_data = array(
                    'other_account_name' => "Credit",
                    'account_id' => $accounts_data->id,
                    'account_type' => 'Credit. Inventory',
                    'entry_code' => $credit_no,
                    'ref' => $credit_no,
                    'details' => " Credit Note" . $results[0]['invoice']['customer']['company_name'],
                    'debit_amount' =>  $total_debit - $total_vat,
                    'description' => "Credit Note",
                    'entry_date' =>  date('Y-m-d'),


                );

                GeneralLedger::create($insert_data + Parent::user_branch_id());
            }
        }
    }
    function creditCustLedger($credit_no)
    {
        $results = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->get();
        $res2 = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->first();
        $customer_id =  $res2->invoice->customer_id;
        $total_debit = 0;
        if (count($results)) {
            foreach ($results as  $value) {

                $total_debit += $value['line_total'];
            }
        }

        $res =  CustomerLedger::where(['customer_id' => $customer_id])->orderBy('created_at', 'desc')->first();

        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_balance = $balance - $total_debit;
        $date = date('Y-m-d');
        $insert_data = array(

            'customer_id' => $customer_id,
            'ref' => $res2->credit_no,
            'type' => 'CN',
            'entry_date' => $date,
            'description' => 'Credit',
            'credit' => $total_debit,
            'amount_due' => $total_debit,
            'balance' => $total_balance
        );
        CustomerLedger::create($insert_data + Parent::user_branch_id());

        $cust = Customer::where('id', $customer_id)->first();
    }

    function debitCustLedger($credit_no)
    {
        $results = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->get();
        $res2 = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->first();
        $customer_id =  $res2->invoice->customer_id;
        $total_debit = 0;
        if (count($results)) {
            foreach ($results as  $value) {

                $total_debit += $value['line_total'];
            }
        }

        $res =  CustomerLedger::where(['customer_id' => $customer_id])->orderBy('created_at', 'desc')->first();

        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_balance = $balance + $total_debit;
        $date = date('Y-m-d');
        $insert_data = array(

            'customer_id' => $customer_id,
            'ref' => $res2->credit_no,
            'type' => 'CN',
            'entry_date' => $date,
            'description' => 'Cancelled Credit',
            'debit' => $total_debit,
            'amount_due' => $total_debit,
            'balance' => $total_balance
        );
        CustomerLedger::create($insert_data + Parent::user_branch_id());
    }
    function reduceOutputVATAccount($credit_no)
    {

        $results = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->get();
        $res2 = CreditNote::where(["credit_no" => $credit_no, 'approved' => 1])->with('invoice.customer')->first();
        $customer_id =  $res2->invoice->customer_id;
        $total_vat = 0;
        if (count($results)) {
            foreach ($results as  $value) {

                $total_vat += $value['row_vat'];
            }
        }





        $customer = Customer::where('id', $customer_id)->first();




        $accounts_data =  LedgerAccount::where(['account_type' => "output VAT"])->first();
        if ($total_vat == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }


        $insert_data = array(
            'other_account_name' => $customer->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'CREDIT note',
            'entry_code' => $res2->credit_no,
            'ref' => $res2->credit_no,
            'details' => "CREDIT NOTE ",
            'debit_amount' => $total_vat,
            'description' => "Credinote VAT",
            'entry_date' => date('Y-m-d'),

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    public function cancel(Request $request)
    {
        DB::transaction(function () use ($request) {
            $credit_no = $request->credit_no;
            $this->debitCustLedger($credit_no);
            $results = CreditNote::where('credit_no', $credit_no)->get();
            if (count($results)) {
                foreach ($results as  $value) {
                    $data_model = Invoice::find($value['invoice_id']);
                    $data_model->qty += $value['qty'];
                    $data_model->tax_amount += $value['row_vat'];
                    $data_model->row_total += ($value['price'] * $value['qty']);
                    $data_model->save();
                    if ($value['reduce_stock']) {
                        if (Parent::isInventory($value['invoice']['stock_id'])) {
                            Inventory::where([
                                'department_id' => $value['department_id'],
                                'stock_id' => $value['invoice']['stock_id'],

                            ])->decrement('qty', $value['qty']);
                        }
                    }
                }
            }
            Invoice::where(['invoice_no' => $request->invoice_no])
                ->update(['unpaid_amount' => DB::raw('unpaid_amount +' .  $request->credit_total)]);
            CreditNote::where('credit_no', $credit_no)->delete();
            GeneralLedger::where('entry_code', $credit_no)->delete();
        }, 5);
        return true;
    }
    public function fetchByCreditNo(Request $request)
    {
        $credit_no = $request->route('credit_no');
        $results = CreditNote::where('credit_no', $credit_no)->with(['invoice.stock','user'])->get();
        return response()->json(['results' => $results]);
    }




    public function generateCredit(Request $request)
    {
        $credit_no = $request->route('credit_no');
        $data = CreditNote::where('credit_no', $credit_no)->with(['user', 'branch', 'invoice.stock', 'invoice.customer'])->get();
        $path = public_path() . '/pdf/' . $credit_no . '.pdf';
        $pdf = PDF::loadView('pdf.credit', compact('data'));

        $pdf->save($path);

        return response()->download($path);
    }






    function generateCreditNo($invoice_no)
    {


        $saved_creditno = CreditNote::where(['invoice_no' => $invoice_no, 'approved' => 0])->first();

        if ($saved_creditno) {
            return $saved_creditno->credit_no;
        }
        $latest = CreditNote::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $creditNo = $today . '-' . 'CR' . sprintf('%04d', $string);
        $isUnique = CreditNote::where('credit_no', $creditNo)->first();
        if ($isUnique) {
            $creditNo = $today . '-' . 'CR' . ($latest->id + 1);
        }
        return  $creditNo;
    }
    function saveNotes(Request $request)
    {
        $this->validate($request, [

            'notes' => 'required|max:length:200',
            'credit_no' => 'required',
        ]);
        CreditNote::where('credit_no', $request->credit_no)->update(['notes' => $request->notes]);
        return true;
    }
}
