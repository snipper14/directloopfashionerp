<?php

namespace App\Http\Controllers\GeneralLedger;

use Carbon\Carbon;
use App\Invoices\Invoice;
use App\Expenses\Expenses;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\PurchaseOrder\PurchaseOrderReceivable;

class BalanceSheetController extends BaseController
{
    protected function computeProfitLossAmount(string $from, string $to): float
    {
        $totalSales      = (float) $this->computeTotalSales($from, $to);
        $totalExpenses   = (float) $this->computeTotalExpenses($from, $to);
        $totalCogs       = (float) $this->computeTotalCogs($from, $to);
        $invoiceIncome   = (float) $this->computeInvoiceIncome($from, $to);

        $profitLoss = ($totalSales + $invoiceIncome) - ($totalCogs + $totalExpenses);
        return (float) ($profitLoss ?? 0);
    }

    protected function computeTotalSales(string $from, string $to): float
    {
        $q = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->selectRaw('SUM(receipt_total) AS sum_receipt_total')
            ->first();

        return (float) ($q->sum_receipt_total ?? 0);
    }

    protected function computeInvoiceIncome(string $from, string $to): float
    {
        $q = Invoice::when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->selectRaw('SUM(row_total) AS sum_total')
            ->first();

        return (float) ($q->sum_total ?? 0);
    }

    protected function computeTotalCogs(string $from, string $to): float
    {
        $inv = Invoice::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('invoice_date', [$from, $to]);
            })
            ->selectRaw('SUM(qty * purchase_price) AS invoice_cogs')
            ->first();

        $invoiceCogs = (float) ($inv->invoice_cogs ?? 0);

        $pos = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->selectRaw('SUM(cost_price * qty) AS pos_cogs')
            ->first();

        $posCogs = (float) ($pos->pos_cogs ?? 0);

        return $invoiceCogs + $posCogs;
    }

    protected function computeTotalExpenses(string $from, string $to): float
    {
        $glRows = GeneralLedger::query()
            ->join('ledger_accounts', 'general_ledgers.account_id', '=', 'ledger_accounts.id')
            ->where('ledger_accounts.account_type', 'expenses')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('general_ledgers.entry_date', [$from, $to]);
            })
            ->selectRaw("
                ledger_accounts.id   as ledger_account_id,
                COALESCE(SUM(general_ledgers.debit_amount - general_ledgers.credit_amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id')
            ->get();

        $expRows = Expenses::withoutGlobalScope(BranchScope::class)
            ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
            ->join('ledger_accounts', 'expense_types.ledger_account_id', '=', 'ledger_accounts.id')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('expenses.date_recorded', [$from, $to]);
            })
            ->selectRaw("
                ledger_accounts.id as ledger_account_id,
                COALESCE(SUM(expenses.amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id')
            ->get();

        $byAccount = [];
        $add = function ($row) use (&$byAccount) {
            $id = $row->ledger_account_id;
            if (!isset($byAccount[$id])) {
                $byAccount[$id] = 0.0;
            }
            $byAccount[$id] += (float) $row->sum_expenses;
        };

        foreach ($glRows as $r)  { $add($r); }
        foreach ($expRows as $r) { $add($r); }

        return array_sum($byAccount);
    }

    protected function fetchLedgerAccountsByTypes(array $accountTypes, string $from, string $to): array
    {
        $accounts = LedgerAccount::whereIn('ledger_accounts.account_type', $accountTypes)
            ->join('ledger_sub_accounts', 'ledger_accounts.ledger_sub_account_id', '=', 'ledger_sub_accounts.id')
            ->select('ledger_accounts.ledger_sub_account_id', 'ledger_sub_accounts.name', 'ledger_accounts.account_type')
            ->get()
            ->groupBy('account_type');

        $data = [];
        foreach ($accountTypes as $type) {
            $data[$type] = [];
            $subAccountIds = $accounts->has($type) ? $accounts[$type]->pluck('ledger_sub_account_id')->unique()->toArray() : [];

            if (!empty($subAccountIds)) {
                $balances = GeneralLedger::whereIn('account_id', function ($query) use ($subAccountIds) {
                        $query->select('id')
                            ->from('ledger_accounts')
                            ->whereIn('ledger_sub_account_id', $subAccountIds);
                    })
                    ->when($from && $to, function ($query) use ($from, $to) {
                        $query->whereBetween('entry_date', [$from, $to]);
                    })
                    ->selectRaw('ledger_accounts.ledger_sub_account_id, COALESCE(SUM(debit_amount - credit_amount), 0) as amount')
                    ->join('ledger_accounts', 'general_ledgers.account_id', '=', 'ledger_accounts.id')
                    ->groupBy('ledger_accounts.ledger_sub_account_id')
                    ->get()
                    ->keyBy('ledger_sub_account_id');

                foreach ($accounts[$type]->unique('ledger_sub_account_id') as $account) {
                    $amount = $balances->has($account->ledger_sub_account_id) ? (float) $balances[$account->ledger_sub_account_id]->amount : 0.0;
                    if (in_array($type, ['Current Liability','Equity', 'Long Term Liability', 'output VAT', 'WHT', 'Late Delivery Charges'])) {
                        $amount = -$amount;
                    }
                    $data[$type][] = [
                        'id' => $account->ledger_sub_account_id,
                        'account' => $account->name,
                        'amount' => $amount,
                    ];
                }
            }
        }

        return $data;
    }

    protected function fetchLedgerAccountsByTypesPrev(array $accountTypes, string $from, string $to): array
    {
        $accounts = LedgerAccount::whereIn('account_type', $accountTypes)
            ->select('id', 'account', 'account_type')
            ->get()
            ->groupBy('account_type');

        $data = [];
        foreach ($accountTypes as $type) {
            $data[$type] = [];
            $accountIds = $accounts->has($type) ? $accounts[$type]->pluck('id')->toArray() : [];

            if (!empty($accountIds)) {
                $balances = GeneralLedger::whereIn('account_id', $accountIds)
                    ->when($from && $to, function ($query) use ($from, $to) {
                        $query->whereBetween('entry_date', [$from, $to]);
                    })
                    ->selectRaw('account_id, COALESCE(SUM(debit_amount - credit_amount), 0) as amount')
                    ->groupBy('account_id')
                    ->get()
                    ->keyBy('account_id');

                foreach ($accounts[$type] as $account) {
                    $amount = $balances->has($account->id) ? (float) $balances[$account->id]->amount : 0.0;
                    if (in_array($type, ['Current Liability', 'Long Term Liability', 'output VAT', 'WHT', 'Late Delivery Charges'])) {
                        $amount = -$amount;
                    }
                    $data[$type][] = [
                        'id' => $account->id,
                        'account' => $account->account,
                        'amount' => $amount,
                    ];
                }
            }
        }

        return $data;
    }

    protected function inventoryValue(string $from, string $to)
    {
        $main_query = Inventory::withoutGlobalScope(BranchScope::class)
            ->join('stocks', 'inventories.stock_id', '=', 'stocks.id')
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('inventories.created_at', [$from, $to]);
            })
            ->selectRaw('inventories.id, SUM(inventories.qty) AS sum_qty, SUM(stocks.purchase_price * inventories.qty) AS value_on_pp')
            ->first();

        return [
            'id' => uniqid($main_query->id ?? 'inv', true),
            'account' => 'Inventory',
            'amount' => (float) ($main_query->value_on_pp ?? 0),
        ];
    }

    protected function accountReceivableDr(string $from, string $to)
    {
        $res = CustomerLedger::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('entry_date', [$from, $to]);
            })
            ->selectRaw('SUM(debit - credit) AS total_balance')
            ->first();

        return [
            'id' => uniqid(),
            'account' => 'Accounts Receivable',
            'amount' => (float) ($res->total_balance ?? 0),
        ];
    }

    protected function accountPayable(string $from, string $to)
    {
        $res = CreditLedger::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('entry_date', [$from, $to]);
            })
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('supplier', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->selectRaw('SUM(credit - debit) AS actual_balance')
            ->first();

        return [
            'id' => uniqid(),
            'account' => 'Accounts Payable',
            'amount' => (float) ($res->actual_balance ?? 0),
        ];
    }

    protected function getVatAmount(string $from, string $to)
    {
        $sales_vat = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('paid_date', [$from, $to]);
            })
            ->selectRaw('SUM(total_vat) as amount')
            ->first();

        $purchase_vat = PurchaseOrderReceivable::withoutGlobalScope(BranchScope::class)
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('received_date', [$from, $to]);
            })
            ->selectRaw('SUM(tax_amount) as amount')
            ->first();

        $total_sale_vat = (float) ($sales_vat->amount ?? 0);
        $total_purchase_vat = (float) ($purchase_vat->amount ?? 0);

        return [
            'id' => uniqid(),
            'account' => 'Vat Amount',
            'amount' => $total_sale_vat - $total_purchase_vat,
        ];
    }

    protected function fetchExpenses(string $from, string $to)
    {
        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)
            ->with(['expense_type'])
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween('date_recorded', [$from, $to]);
            })
            ->selectRaw('SUM(amount) AS sum_expenses')
            ->first();

        $total_expenses = (float) ($raw_query->sum_expenses ?? 0);
        return [
            'id' => uniqid(),
            'account' => 'Expenses',
            'amount' => $total_expenses,
        ];
    }

    protected function computeYearlyBalanceSheet(string $from, string $to): array
    {
        request()->merge(['from' => $from, 'to' => $to]);

        $accountTypes = [
            'Fixed Asset',
            'Current Asset',
            'Other Current Asset',
            'Current Liability',
            'Long Term Liability',
            'Equity',
            'input VAT',
            'output VAT',
            'WHT',
            'Late Delivery Charges',
        ];
        $ledgerData = $this->fetchLedgerAccountsByTypes($accountTypes, $from, $to);

        $fixedAssets = $ledgerData['Fixed Asset'] ?? [];
        $currentAssets = array_merge(
            $ledgerData['Current Asset'] ?? [],
            [$this->inventoryValue($from, $to), $this->accountReceivableDr($from, $to), $ledgerData['input VAT'][0] ?? ['id' => 0, 'account' => 'input VAT', 'amount' => 0]]
        );
        $otherCurrentAssets = $ledgerData['Other Current Asset'] ?? [];
        $currentLiabilities = array_merge(
            $ledgerData['Current Liability'] ?? [],
            [
                $this->accountPayable($from, $to),
                $ledgerData['output VAT'][0] ?? ['id' => 0, 'account' => 'output VAT', 'amount' => 0],
                $ledgerData['WHT'][0] ?? ['id' => 0, 'account' => 'WHT', 'amount' => 0],
                $ledgerData['Late Delivery Charges'][0] ?? ['id' => 0, 'account' => 'Late Delivery Charges', 'amount' => 0],
            ]
        );
        $longTermLiabilities = $ledgerData['Long Term Liability'] ?? [];
        $equity = $ledgerData['Equity'] ?? [];
        $profitLossAmount = (float) $this->computeProfitLossAmount($from, $to);

        $sum = fn(array $rows) => array_reduce($rows, fn($s, $r) => $s + (float) ($r['amount'] ?? 0), 0.0);

        $totals = [
            'total_fixed_asset' => $sum($fixedAssets),
            'total_current_asset' => $sum($currentAssets),
            'total_other_current_asset' => $sum($otherCurrentAssets),
            'total_current_liability' => $sum($currentLiabilities),
            'total_longterm_liability' => $sum($longTermLiabilities),
            'total_equity' => $sum($equity),
            'profit_loss_amount' => $profitLossAmount,
        ];

        $totals['total_assets'] = $totals['total_fixed_asset'] + $totals['total_current_asset'] + $totals['total_other_current_asset'];
        $totals['total_liabilities_and_equity'] = $totals['total_current_liability'] + $totals['total_longterm_liability'] + $totals['total_equity'] + $totals['profit_loss_amount'];

        return [
            'from' => $from,
            'to' => $to,
            'sections' => [
                'fixed_assets' => $fixedAssets,
                'current_assets' => $currentAssets,
                'other_current_assets' => $otherCurrentAssets,
                'current_liabilities' => $currentLiabilities,
                'long_term_liabilities' => $longTermLiabilities,
                'equity' => $equity,
            ],
            'totals' => $totals,
        ];
    }

    public function summary(Request $request)
    {
        $from = $request->input('from', '1970-01-01 00:00:00');
        $to = $request->input('to', now(config('app.timezone', 'Africa/Nairobi'))->format('Y-m-d H:i:s'));

        // Compute balance sheet for the provided date range
        $currentData = $this->computeYearlyBalanceSheet($from, $to);

        // Compute comparison for the last 3 years
        $toDate = Carbon::parse($to);
        $currentYear = $toDate->year;
        $comparison = [];

        // Current Year (YTD)
        $comparison[] = [
            'year' => $currentYear,
            'data' => $this->computeYearlyBalanceSheet(
                "{$currentYear}-01-01 00:00:00",
                $to
            )
        ];

        // Previous Year 1
        $prevYear1 = $currentYear - 1;
        $comparison[] = [
            'year' => $prevYear1,
            'data' => $this->computeYearlyBalanceSheet(
                "{$prevYear1}-01-01 00:00:00",
                "{$prevYear1}-12-31 23:59:59"
            )
        ];

        // Previous Year 2
        $prevYear2 = $currentYear - 2;
        $comparison[] = [
            'year' => $prevYear2,
            'data' => $this->computeYearlyBalanceSheet(
                "{$prevYear2}-01-01 00:00:00",
                "{$prevYear2}-12-31 23:59:59"
            )
        ];

        return response()->json([
            'from' => $from,
            'to' => $to,
            'sections' => $currentData['sections'],
            'totals' => $currentData['totals'],
            'comparison' => $comparison,
        ]);
    }
}