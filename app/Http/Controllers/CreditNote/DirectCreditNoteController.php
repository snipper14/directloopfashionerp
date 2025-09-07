<?php

namespace App\Http\Controllers\CreditNote;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use Illuminate\Support\Facades\DB;
use App\CreditNote\DirectCreditNote;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreditNote\DirectCreditNoteRequest;

class DirectCreditNoteController extends BaseController
{

    function create(DirectCreditNoteRequest $request)
    {
        DirectCreditNote::updateOrCreate(['credit_no' => $request->credit_no, 'stock_id' => $request->stock_id], $request->validated() +

            Parent::user_branch_id());
        $res = DirectCreditNote::with(['stock'])->where(['status' => 'progress', 'customer_id' => $request->customer_id])->latest()->get();
        return $res;
    }

    function complete(Request $request)
    {
        DB::transaction(function () use ($request) {
            $credit_no = $this->generateCreditNo();
            $insert_data = array(

                'customer_id' => $request->customer_id,
                'ref' => $credit_no,
                'type' => 'CN',
                'entry_date' => $request->credit_date,
                'description' => 'Credit',
                'credit' => $request->total_amount,
                'amount_due' => 0,
                'balance' => 0
            );
            CustomerLedger::create($insert_data + Parent::user_branch_id());

            DirectCreditNote::where(['credit_no' => $request->credit_no])->update([
                'status' => 'complete', 'credit_no' => $credit_no
            ]);
        }, 5);
    }

    function generateCreditNo()
    {
        $res = DirectCreditNote::where(['status' => 'complete'])->latest()->first();
        $creditNoteNo = 1;
        if ($res) {
            $creditNoteNo = $res->credit_no + 1;
        }

        return  $creditNoteNo;
    }
    function fetchRandCreditNoteNo()
    {
        $latest = DirectCreditNote::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $creditNo = $today . '-' . 'CR' . sprintf('%04d', $string);
        $isUnique = DirectCreditNote::where('credit_no', $creditNo)->first();
        if ($isUnique) {
            $creditNo = $today . '-' . 'CR' . ($latest->id + 1);
        }
        return  $creditNo;
    }
    function fetchPendingCr()
    {
        $res = DirectCreditNote::with(['stock'])->where(['status' => 'progress', 'customer_id' => request('customer_id')])->latest()->get();
        //  $res = DirectCreditNote::with(['customer'])->where(['customer_id' => request('customer_id'), 'status' => 'progress'])->get();
        $data = [
            'res' => $res,
            'credit_no' => $this->fetchRandCreditNoteNo()
        ];
        return $data;
    }
    function destroy(Request $request)
    {
        DirectCreditNote::where(['id' => $request->route('id')])->delete();
        return true;
    }

    function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = DirectCreditNote::with(['stock', 'customer', 'user'])

            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('credit_no', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('notes', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('customer', function ($query) {
                            $query->where('company_name', 'LIKE', '%' . request('search') . '%');
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
            ->where(['status' => 'complete'])->selectRaw("*,SUM(line_total) AS sum_line_total,SUM(row_vat) AS sum_row_vat")->groupBy('credit_no')->latest();

        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(30);
        }
        return response()->json($data);
    }
    function fetchDetails()
    {
        $data = DirectCreditNote::with(['stock', 'customer', 'user', 'branch'])
            ->where(['credit_no'=>request('credit_no')])->get();

        return response()->json($data);
    }
}
