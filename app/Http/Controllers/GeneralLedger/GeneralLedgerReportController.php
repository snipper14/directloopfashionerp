<?php

namespace App\Http\Controllers\GeneralLedger;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Pagination\LengthAwarePaginator;

class GeneralLedgerReportController extends BaseController
{


public function generalLedgerStatement(Request $request)
{
    $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : null;
    $to   = $request->filled('to')   ? date('Y-m-d', strtotime($request->to))   : null;

    $q = GeneralLedger::query()
        // filters BEFORE grouping
        ->when($request->filled('search'), function ($query) use ($request) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ref', 'like', "%$s%")
                  ->orWhere('debit_amount', 'like', "%$s%")
                  ->orWhere('credit_amount', 'like', "%$s%")
                  ->orWhereHas('account', fn($qq) => $qq->where('account', 'like', "%$s%"))
                  ->orWhereHas('user', fn($qq) => $qq->where('name', 'like', "%$s%"));
            });
        })
        ->when($from && $to, fn($query) => $query->whereBetween('entry_date', [$from, $to]))
        // aggregate by account
        ->selectRaw('
            general_ledgers.account_id,
            SUM(general_ledgers.debit_amount)  AS debit_total,
            SUM(general_ledgers.credit_amount) AS credit_total,
            SUM(general_ledgers.debit_amount - general_ledgers.credit_amount) AS total_balance
        ')
        ->groupBy('general_ledgers.account_id')
        // 👉 this makes `data.account.account` available
        ->with(['account:id,account']);

    // OPTIONAL: sort by account name without breaking the group
    if (in_array($request->sort_account_name, ['ASC','DESC'])) {
        $q->orderBy(
            LedgerAccount::select('account')
                ->whereColumn('ledger_accounts.id', 'general_ledgers.account_id')
                ->limit(1),
            $request->sort_account_name
        );
    }

    if (in_array($request->sort_total_balance, ['ASC','DESC'])) {
        $q->orderBy('total_balance', $request->sort_total_balance);
    }

    if (!$request->sort_account_name && !$request->sort_total_balance) {
        $q->orderBy('general_ledgers.account_id');
    }

    return request('page') > 0 ? $q->paginate(30) : $q->get();
}


    function queryStatement()
    {
        $id = request('account_id');
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  GeneralLedger::with(['user', 'branch', 'account'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('debit_amount', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('credit_amount', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('account', function ($q) {
                        $q->where('account', 'LIKE', '%' . request('search') . '%');
                    })

                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->where(['account_id' => $id]);

        if (request('page') > 0) {
            $paginatedData = $main_query->paginate(100);
        } else {
            $paginatedData = $main_query->get();
        }
        return $paginatedData;
    }
    function paginatedStatement()
    {
        $paginatedData = $this->queryStatement();
        $data = [];
        $current_total = 0;
        foreach ($paginatedData as  $value) {

            $current_total += $value->debit_amount;
            $current_total -= $value->credit_amount;


            $data[] = [
                'id' => $value->id,
                'ref' => $value->ref,
                'entry_date' => $value->entry_date,
                'type' => $value->type,
                'description' => $value->description,
                'debit' => $value->debit_amount,
                'credit' => $value->credit_amount,
                'account' => $value->account,
                'account_type' => $value->account_type,
                'user' => $value->user,
                'branch' => $value->branch,
                "balance" => $current_total,
                'entry_code' => $value->entry_code,
                'other_account_name' => $value->other_account_name,
                'details' => $value->details
            ];
        }
        return $data;
    }
private function baseLedgerQueryDesc(Request $request)
{
    $id   = $request->account_id;
    $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : null;
    $to   = $request->filled('to')   ? date('Y-m-d', strtotime($request->to))   : null;

    return GeneralLedger::with(['user','branch','account'])
        ->when($request->filled('search'), function ($query) use ($request) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ref', 'like', "%$s%")
                  ->orWhere('type', 'like', "%$s%")
                  ->orWhere('debit_amount', 'like', "%$s%")
                  ->orWhere('credit_amount', 'like', "%$s%")
                  ->orWhereHas('account', fn($qq) => $qq->where('account', 'like', "%$s%"))
                  ->orWhereHas('user', fn($qq) => $qq->where('name', 'like', "%$s%"));
            });
        })
        ->when($from && $to, fn($q) => $q->whereBetween('entry_date', [$from, $to]))
        ->where('account_id', $id)
        // 👇 newest first (going backwards)
        ->orderBy('entry_date', 'desc')
        ->orderBy('id', 'desc');
}

public function fetchLedgerStatement(Request $request)
{
    $perPage = max(1, (int) $request->input('per_page', 100));

    // same filters & ORDER (DESC)
    $base = $this->baseLedgerQueryDesc($request);

    // current page
    $pageQuery = (clone $base);
    $paginated = $pageQuery->paginate($perPage);

    if ($paginated->isEmpty()) {
        return response()->json($paginated);
    }

    // 1) Closing balance across ALL filtered rows
    $closing = (clone $base)
        ->selectRaw('COALESCE(SUM(debit_amount - credit_amount), 0) AS t')
        ->value('t');

    // 2) First row on this DESC page
    $first = $paginated->first();

    // 3) Sum of rows that are NEWER than the first row on this page
    //    (because we are going backwards)
    $sumNewer = (clone $base)
        ->where(function ($q) use ($first) {
            $q->where('entry_date', '>', $first->entry_date)
              ->orWhere(function ($qq) use ($first) {
                  $qq->where('entry_date', $first->entry_date)
                     ->where('id', '>', $first->id);
              });
        })
        ->selectRaw('COALESCE(SUM(debit_amount - credit_amount), 0) AS s')
        ->value('s');

    // 4) Page starting balance = closing - sum(newer than first row)
    $running = (float) $closing - (float) $sumNewer;

    // 5) Build rows, subtracting deltas as we go backwards
    $rows = [];
    foreach ($paginated as $gl) {
        $delta = (float)$gl->debit_amount - (float)$gl->credit_amount;

        // Balance shown as "after this row" (typical for running balance)
        $rows[] = [
            'id'                => $gl->id,
            'ref'               => $gl->ref,
            'entry_date'        => $gl->entry_date,
            'type'              => $gl->type,
            'description'       => $gl->description,
            'debit'             => $gl->debit_amount,
            'credit'            => $gl->credit_amount,
            'account'           => $gl->account,
            'account_type'      => $gl->account_type,
            'user'              => $gl->user,
            'branch'            => $gl->branch,
            'entry_code'        => $gl->entry_code,
            'other_account_name'=> $gl->other_account_name,
            'details'           => $gl->details,
            'balance'           => $running, // after applying this (latest) row
        ];

        // move backwards: next (older) row has prior balance
        $running -= $delta;
    }

    $result = new LengthAwarePaginator(
        $rows,
        $paginated->total(),
        $paginated->perPage(),
        $paginated->currentPage(),
        ['path' => url()->current(), 'query' => request()->query()]
    );

    return response()->json($result);
}
    
    function deleteStatement(Request $request)
    {
        $id = $request->route('id');
        GeneralLedger::where('id', $id)->delete();
        return true;
    }

    function fetchAll()
    {
        $data = $this->paginatedStatement();
        return response()->json($data);
    }
    public function downloadStatment(Request $request)
    {
        $id = request('customer_id');
        $data = $this->paginatedStatement();

        $current = $this->datePeriodReport(0); //$current_res ? $current_res->balance : 0;

        $thirty = $this->datePeriodReport(30);

        $sixty =  $this->datePeriodReport(60);
        $ninety =  $this->datePeriodReport(90);
        $one_20 =  $this->datePeriodReport(120);
        $one_50 =  $this->datePeriodReport(150);


        $one_80 = $this->datePeriodReport(180);

        $folderPath = public_path('pdf');

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . 'customer_statement' . '.pdf';

        $pdf = PDF::loadView('pdf.general_ledger_statement', [
            'data' => $data,
            'thirty' => $thirty,
            'current' => $current,
            'sixty' => $sixty,
            'ninety' => $ninety,
            'one_20' => $one_20,
            'one_50' => $one_50,
            'one_80' => $one_80,
        ]);
        $pdf->save($path);
        return response()->download($path);
    }
    function datePeriodReport($days)
    {
        $id = request('account_id');
        $res = GeneralLedger::where(['account_id' => $id])->where('entry_date', '<=', Carbon::now()->subDays($days))->get();
        $current_total = 0;

        foreach ($res as  $value) {

            $current_total += $value->debit_amount;
            $current_total -= $value->credit_amount;
        }
        return $current_total;
    }
}
