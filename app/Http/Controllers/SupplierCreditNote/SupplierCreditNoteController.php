<?php

namespace App\Http\Controllers\SupplierCreditNote;

use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\SupplierCreditNote\SupplierCreditNote;
use App\Http\Requests\SupplierCreditNote\SupplierCreditNoteRequest;
use App\SupplierInvoice\SupplierInvoice;

class SupplierCreditNoteController extends BaseController
{
    function create(SupplierCreditNoteRequest $request)
    {
        DB::transaction(function () use ($request) {
        SupplierCreditNote::create($request->validated() + Parent::user_branch_id());
        $this->debitLedger($request);
        SupplierInvoice::where(['invoice_no'=>$request->invoice_no])->update(  ['unpaid_amount' => DB::raw('unpaid_amount -' .  $request->credit_amount)]);
        },5);
        return true;
    }

    public function debitLedger(Request $request)
    {

        $data = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->credit_no,
            'type' => 'CR',
            'entry_date' => $request->credit_date,
            'description' => $request->details,
            'debit' => $request->credit_amount,
            'amount_due' => 0,
            'balance' => 0
        );
        CreditLedger::create($data + Parent::user_branch_id());
    }

    public function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query = SupplierCreditNote::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('credit_no', 'LIKE', '%' . request('search') . '%')->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('supplier_invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['supplier', 'supplier_invoice','user'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('credit_date', [$from, $to]);
                }
            )

            ->orderBy('id', "DESC");
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }
    public function fetchTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query = SupplierCreditNote::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('credit_no', 'LIKE', '%' . request('search') . '%')->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('supplier_invoice', function ($query) {
                        $query->where('invoice_no', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('credit_date', [$from, $to]);
                }
            )
            ->selectRaw('SUM(credit_amount) as sum_credit_amount');

        $res = $main_query->first();

        return response()->json($res);
    }

    public function destroy(Request $request)
    {
       
        SupplierCreditNote::find($request->route('id'))->delete();
       
        return true;
    }
}
