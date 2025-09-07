<?php

namespace App\Http\Controllers\GeneralLedger;

use App\Payroll\Payroll;
use App\Invoices\Invoice;
use App\Expenses\Expenses;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\CreditNote\CreditNote;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Payments\CustomerPayment;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use App\SupplierCreditNote\SupplierCreditNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProfitLossController extends BaseController
{
    function fetchAssets()
    {
        $data = [$this->accountDrInvoiceSales(), $this->fetchDrSupplierCreditNote()];
        return response()->json($data);
    }

    function fetchLiabilities()
    {
        $data = $this->fetchExpenses();
        return response()->json($data);
    }

    private function fetchExpenses($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : null;
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in fetchExpenses: from=$from, to=$to");
                return [];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in fetchExpenses: from=$from, to=$to, error=" . $e->getMessage());
            return [];
        }

        $glRows = GeneralLedger::query()
            ->join('ledger_accounts', 'general_ledgers.account_id', '=', 'ledger_accounts.id')
            ->where('ledger_accounts.account_type', 'expenses')
            ->when($startOfMonth && $endOfMonth, function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('general_ledgers.entry_date', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw("
                ledger_accounts.id as ledger_account_id,
                ledger_accounts.account as ledger_account_name,
                ledger_accounts.account_no as ledger_account_no,
                COALESCE(SUM(general_ledgers.debit_amount - general_ledgers.credit_amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
            ->get();

        $expRows = Expenses::withoutGlobalScope(BranchScope::class)
            ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
            ->join('ledger_accounts', 'expense_types.ledger_account_id', '=', 'ledger_accounts.id')
            ->when($startOfMonth && $endOfMonth, function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('expenses.date_recorded', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw("
                ledger_accounts.id as ledger_account_id,
                ledger_accounts.account as ledger_account_name,
                ledger_accounts.account_no as ledger_account_no,
                COALESCE(SUM(expenses.amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
            ->get();

        $totals = [];
        $adder = function ($row) use (&$totals) {
            $id = $row->ledger_account_id;
            if (!isset($totals[$id])) {
                $totals[$id] = [
                    'ledger_account_id' => $id,
                    'ledger_account_name' => $row->ledger_account_name,
                    'ledger_account_no' => $row->ledger_account_no,
                    'sum_expenses' => 0.0,
                ];
            }
            $totals[$id]['sum_expenses'] += (float)$row->sum_expenses;
        };

        foreach ($glRows as $r) $adder($r);
        foreach ($expRows as $r) $adder($r);

        $formatAccountNo = function (?string $value) {
            if (!$value) return $value;
            $parts = explode('-', $value);
            if (isset($parts[0]) && strlen($parts[0]) === 1) {
                $parts[0] .= '00';
            }
            return implode('-', $parts);
        };

        $data = [];
        foreach ($totals as $t) {
            $prettyNo = $formatAccountNo($t['ledger_account_no']);
            $data[] = [
                'type' => 'dr',
                'account' => "{$prettyNo} . {$t['ledger_account_name']}",
                'dr_amount' => $t['sum_expenses'],
                'cr_amount' => 0.0,
            ];
        }

        return $data;
    }

    private function cumulativeExpenses()
    {
        $glRows = GeneralLedger::query()
            ->join('ledger_accounts', 'general_ledgers.account_id', '=', 'ledger_accounts.id')
            ->where('ledger_accounts.account_type', 'expenses')
            ->selectRaw("
                ledger_accounts.id as ledger_account_id,
                ledger_accounts.account as ledger_account_name,
                ledger_accounts.account_no as ledger_account_no,
                COALESCE(SUM(general_ledgers.debit_amount - general_ledgers.credit_amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
            ->get();

        $expRows = Expenses::withoutGlobalScope(BranchScope::class)
            ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
            ->join('ledger_accounts', 'expense_types.ledger_account_id', '=', 'ledger_accounts.id')
            ->selectRaw("
                ledger_accounts.id as ledger_account_id,
                ledger_accounts.account as ledger_account_name,
                ledger_accounts.account_no as ledger_account_no,
                COALESCE(SUM(expenses.amount), 0) as sum_expenses
            ")
            ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
            ->get();

        $totals = [];
        $adder = function ($row) use (&$totals) {
            $id = $row->ledger_account_id;
            if (!isset($totals[$id])) {
                $totals[$id] = [
                    'ledger_account_id' => $id,
                    'ledger_account_name' => $row->ledger_account_name,
                    'ledger_account_no' => $row->ledger_account_no,
                    'sum_expenses' => 0.0,
                ];
            }
            $totals[$id]['sum_expenses'] += (float)$row->sum_expenses;
        };

        foreach ($glRows as $r) $adder($r);
        foreach ($expRows as $r) $adder($r);

        $formatAccountNo = function (?string $value) {
            if (!$value) return $value;
            $parts = explode('-', $value);
            if (isset($parts[0]) && strlen($parts[0]) === 1) {
                $parts[0] .= '00';
            }
            return implode('-', $parts);
        };

        $data = [];
        $totalExpenses = 0.0;
        foreach ($totals as $t) {
            $prettyNo = $formatAccountNo($t['ledger_account_no']);
            $data[] = [
                'type' => 'dr',
                'account' => "{$prettyNo} . {$t['ledger_account_name']}",
                'dr_amount' => $t['sum_expenses'],
                'cr_amount' => 0.0,
            ];
            $totalExpenses += (float)$t['sum_expenses'];
        }

        return ['expenses' => $data, 'total_expenses' => $totalExpenses];
    }

    function formatAccountNo($value)
    {
        if (!isset($value->ledger_account_no)) return null;
        $parts = explode('-', $value->ledger_account_no);
        if (isset($parts[0]) && strlen($parts[0]) === 1) {
            $parts[0] .= '00';
        }
        return implode('-', $parts);
    }

    function profitLossAmount()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in profitLossAmount: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in profitLossAmount: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)
            ->with(['expense_type'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date_recorded', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(amount) AS sum_expenses');

        $res = $raw_query->first();
        $total_expenses = $res ? $res->sum_expenses : 0;

        $profit_loss = ($this->totalSales($from, $to) + $this->incomeFromInvoicePayments($from, $to)) -
            ($this->totalCostOfGoodsSold($from, $to) + $total_expenses);
        return $profit_loss;
    }

    function fetchCrCustomerCreditNote()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in fetchCrCustomerCreditNote: from=$from, to=$to");
                return ['type' => 'cr', 'account' => "Customer Credit Notes", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in fetchCrCustomerCreditNote: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'cr', 'account' => "Customer Credit Notes", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $main_query = CreditNote::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('credit_date', [$startOfMonth, $endOfMonth]);
        });
        $res = $main_query->selectRaw('SUM(line_total) AS actual_balance')->first();
        return ['type' => 'cr', 'account' => "Customer Credit Notes", 'dr_amount' => 0, 'cr_amount' => $res->actual_balance ?? 0];
    }

    function fetchDrSupplierCreditNote()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in fetchDrSupplierCreditNote: from=$from, to=$to");
                return ['type' => 'dr', 'account' => "Supplier Credit Notes", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in fetchDrSupplierCreditNote: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'dr', 'account' => "Supplier Credit Notes", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $main_query = SupplierCreditNote::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('credit_date', [$startOfMonth, $endOfMonth]);
        });
        $res = $main_query->selectRaw('SUM(credit_amount) AS actual_balance')->first();
        return ['type' => 'dr', 'account' => "Supplier Credit Notes", 'dr_amount' => $res->actual_balance ?? 0, 'cr_amount' => 0];
    }

    function accountCrSupplierInvoices()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in accountCrSupplierInvoices: from=$from, to=$to");
                return ['type' => 'cr', 'account' => "Product/Assets/Service Acquisitions", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in accountCrSupplierInvoices: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'cr', 'account' => "Product/Assets/Service Acquisitions", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $raw_query = SupplierInvoice::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
        })
            ->selectRaw('SUM(invoice_total) AS sum_invoice');

        $res = $raw_query->first();
        return ['type' => 'cr', 'account' => "Product/Assets/Service Acquisitions", 'dr_amount' => 0, 'cr_amount' => $res->sum_invoice ?? 0];
    }

    function accountCrExpenses()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in accountCrExpenses: from=$from, to=$to");
                return ['type' => 'cr', 'account' => "Expenses", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in accountCrExpenses: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'cr', 'account' => "Expenses", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date_recorded', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(amount) AS sum_expenses');

        $res = $raw_query->first();
        return ['type' => 'cr', 'account' => "Expenses", 'dr_amount' => 0, 'cr_amount' => $res->sum_expenses ?? 0];
    }

    public function accountDrInvoiceSales()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in accountDrInvoiceSales: from=$from, to=$to");
                return ['type' => 'dr', 'account' => "Sales", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in accountDrInvoiceSales: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'dr', 'account' => "Sales", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $raw_query = Invoice::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
        })
            ->selectRaw('SUM(row_total) AS invoice_total');

        $res = $raw_query->first();
        return ['type' => 'dr', 'account' => "Sales", 'dr_amount' => $res->invoice_total ?? 0, 'cr_amount' => 0];
    }

    function totalSales($from = null, $to = null)
    {
        return $this->calculatePOSSales($from, $to);
    }

    function incomeFromInvoicePayments($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : Carbon::now()->startOfMonth()->format('Y-m');
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : Carbon::now()->endOfMonth()->format('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
                Log::error("Invalid date format in incomeFromInvoicePayments: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $to)->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in incomeFromInvoicePayments: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $query = Invoice::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })
            ->selectRaw('SUM(row_total) AS sum_total');

        $res = $query->first();
        return $res->sum_total ?? 0;
    }

    function totalCostOfGoodsSold($from = null, $to = null)
    {
        return $this->invoiceCostOfGoodsSold($from, $to) + $this->cashSaleCostOfGoodsSold($from, $to);
    }

    function invoiceCostOfGoodsSold($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : Carbon::now()->startOfMonth()->format('Y-m');
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : Carbon::now()->endOfMonth()->format('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
                Log::error("Invalid date format in invoiceCostOfGoodsSold: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $to)->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in invoiceCostOfGoodsSold: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $query = Invoice::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(qty * purchase_price) AS invoice_cost_of_goods_sold');

        $res = $query->first();
        return $res->invoice_cost_of_goods_sold ?? 0;
    }

    function cashSaleCostOfGoodsSold($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : Carbon::now()->startOfMonth()->format('Y-m');
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : Carbon::now()->endOfMonth()->format('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
                Log::error("Invalid date format in cashSaleCostOfGoodsSold: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $to)->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in cashSaleCostOfGoodsSold: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $query = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(cost_price * qty) AS cash_cost_of_good');

        $res = $query->first();
        return $res->cash_cost_of_good ?? 0;
    }

    private function cumulativePOSSales()
    {
        $query = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)
            ->selectRaw('SUM(receipt_total) AS sum_receipt_total');
        $res = $query->first();
        return $res->sum_receipt_total ?? 0;
    }

    private function cumulativeInvoicePayments()
    {
        $query = Invoice::selectRaw('SUM(row_total) AS sum_total');
        $res = $query->first();
        return $res->sum_total ?? 0;
    }

    private function cumulativeCostOfGoodsSold()
    {
        $invoiceCogs = Invoice::withoutGlobalScope(BranchScope::class)
            ->selectRaw('SUM(qty * purchase_price) AS invoice_cost_of_goods_sold')
            ->first()->invoice_cost_of_goods_sold ?? 0;

        $cashCogs = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)
            ->selectRaw('SUM(cost_price * qty) AS cash_cost_of_good')
            ->first()->cash_cost_of_good ?? 0;

        return $invoiceCogs + $cashCogs;
    }

    public function inventoryCostValue()
    {
        $main_query = Inventory::withoutGlobalScope(BranchScope::class)
            ->join('stocks', 'inventories.stock_id', '=', 'stocks.id')
            ->selectRaw('SUM(inventories.qty) AS sum_qty, SUM(stocks.selling_price * inventories.qty) AS value_on_sp, SUM(stocks.purchase_price * inventories.qty) AS value_on_pp');

        $data = $main_query->first();
        return response()->json($data ? $data->value_on_pp : 0);
    }

    private function calculatePOSSales($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : Carbon::now()->startOfMonth()->format('Y-m');
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : Carbon::now()->endOfMonth()->format('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
                Log::error("Invalid date format in calculatePOSSales: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $to)->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in calculatePOSSales: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $main_query = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(receipt_total) AS sum_receipt_total');

        $res = $main_query->first();
        return $res->sum_receipt_total ?? 0;
    }

    public function drledger()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in drledger: from=$from, to=$to");
                return [];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in drledger: from=$from, to=$to, error=" . $e->getMessage());
            return [];
        }

        $main_query = GeneralLedger::with(['account'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('entry_date', [$startOfMonth, $endOfMonth]);
            })
            ->where('debit_amount', '>', 0)
            ->groupBy('account_id')
            ->orderBy('id', 'ASC');

        $res_data = $main_query->get();
        $data = [];

        foreach ($res_data as $value) {
            $data[] = $this->ledgerDrAccounts($value);
        }
        return $data;
    }

    public function ledgerDrAccounts($value)
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in ledgerDrAccounts: from=$from, to=$to");
                return ['type' => 'dr', 'account' => "Unknown", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in ledgerDrAccounts: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'dr', 'account' => "Unknown", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $main_query = GeneralLedger::with(['account'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('entry_date', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('*, SUM(debit_amount) AS dr_amount')
            ->where('debit_amount', '>', 0)
            ->where('account_id', $value->account_id);

        $res = $main_query->first();
        return ['type' => 'dr', 'account' => $res->account->account ?? 'Unknown', 'dr_amount' => $res->dr_amount ?? 0, 'cr_amount' => 0];
    }

    public function crledger()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in crledger: from=$from, to=$to");
                return [];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in crledger: from=$from, to=$to, error=" . $e->getMessage());
            return [];
        }

        $main_query = GeneralLedger::with(['account'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('entry_date', [$startOfMonth, $endOfMonth]);
            })
            ->where('credit_amount', '>', 0)
            ->groupBy('account_id')
            ->orderBy('id', 'ASC');

        $res_data = $main_query->get();
        $data = [];

        foreach ($res_data as $value) {
            $data[] = $this->ledgerCrAccounts($value);
        }
        return $data;
    }

    public function ledgerCrAccounts($value)
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in ledgerCrAccounts: from=$from, to=$to");
                return null;
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in ledgerCrAccounts: from=$from, to=$to, error=" . $e->getMessage());
            return null;
        }

        $main_query = GeneralLedger::with(['account'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('entry_date', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('*, SUM(credit_amount) AS cr_amount')
            ->where('credit_amount', '>', 0)
            ->where('account_id', $value->account_id);

        $res = $main_query->first();
        return $res ? ['type' => 'cr', 'account' => $res->account->account ?? 'Unknown', 'dr_amount' => 0, 'cr_amount' => $res->cr_amount ?? 0] : null;
    }

    public function taxAmount()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in taxAmount: from=$from, to=$to");
                return ['type' => 'cr', 'account' => "Total Tax", 'dr_amount' => 0, 'cr_amount' => 0];
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in taxAmount: from=$from, to=$to, error=" . $e->getMessage());
            return ['type' => 'cr', 'account' => "Total Tax", 'dr_amount' => 0, 'cr_amount' => 0];
        }

        $raw_query = Invoice::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('invoice_date', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(tax_amount) AS total_tax');

        $res = $raw_query->first();
        $total_tax = ($res->total_tax ?? 0) + $this->calculatePTotalTax($from, $to);
        return ['type' => 'cr', 'account' => "Total Tax", 'dr_amount' => 0, 'cr_amount' => $total_tax];
    }

    function calculatePTotalTax($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : Carbon::now()->startOfMonth()->format('Y-m');
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : Carbon::now()->endOfMonth()->format('Y-m');

            if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
                Log::error("Invalid date format in calculatePTotalTax: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
            $endOfMonth = Carbon::createFromFormat('Y-m', $to)->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in calculatePTotalTax: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $main_query = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('SUM(total_vat) AS sum_vat');

        $res = $main_query->first();
        return $res->sum_vat ?? 0;
    }

    public function totalPayroll()
    {
        try {
            $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : null;
            $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in totalPayroll: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in totalPayroll: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $raw_query = Payroll::when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('payroll_to', [$startOfMonth, $endOfMonth]);
        })
            ->selectRaw('SUM(basic_salary) AS total_basic_salary');

        $res = $raw_query->first();
        return $res->total_basic_salary ?? 0;
    }

    function totalExpenses($from = null, $to = null)
    {
        try {
            $from = $from ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $from)) : null;
            $to = $to ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', $to)) : null;

            if ($from && $to && (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to))) {
                Log::error("Invalid date format in totalExpenses: from=$from, to=$to");
                return 0;
            }

            $startOfMonth = $from ? Carbon::createFromFormat('Y-m', $from)->startOfMonth() : null;
            $endOfMonth = $to ? Carbon::createFromFormat('Y-m', $to)->endOfMonth() : null;
        } catch (\Exception $e) {
            Log::error("Carbon parsing error in totalExpenses: from=$from, to=$to, error=" . $e->getMessage());
            return 0;
        }

        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)
            ->with(['expense_type'])
            ->when($startOfMonth && $endOfMonth, function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date_recorded', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('expense_type_id, SUM(amount) AS sum_expenses')
            ->groupBy('expense_type_id');

        $res = $raw_query->get();
        $total = 0;
        foreach ($res as $value) {
            $total += $value->sum_expenses;
        }

        return $total;
    }

    function profitOrLossAmount()
    {
        $total_sales = (float)($this->totalSales() ?? 0);
        $total_expenses = (float)($this->totalExpenses() ?? 0);
        $total_cogs = (float)($this->totalCostOfGoodsSold() ?? 0);
        $invoice_income = (float)($this->incomeFromInvoicePayments() ?? 0);

        $profit_loss = ($total_sales + $invoice_income) - ($total_cogs + $total_expenses);
        return response()->json($profit_loss);
    }

    public function fetchProfitLoss()
    {
        $from = request('from') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('from'))) : Carbon::now()->startOfMonth()->format('Y-m');
        $to = request('to') ? trim(preg_replace('/\d{4}-\d{2}-\d{2}.*/', '$1', request('to'))) : Carbon::now()->endOfMonth()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
            Log::error("Invalid date format: from=$from, to=$to");
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM'], 422);
        }

        try {
            $fromDate = Carbon::createFromFormat('Y-m', $from);
            $toDate = Carbon::createFromFormat('Y-m', $to);
        } catch (\Exception $e) {
            Log::error("Carbon parsing error: from=$from, to=$to, error=" . $e->getMessage());
            return response()->json(['error' => 'Invalid date format: ' . $e->getMessage()], 422);
        }

        $months = [];
        $current = $fromDate->copy()->startOfMonth();
        while ($current <= $toDate) {
            $months[] = [
                'start' => $current->format('Y-m'),
                'end' => $current->format('Y-m'),
                'label' => $current->format('M Y'),
            ];
            $current->addMonth();
        }
        $monthDiff = $fromDate->diffInMonths($toDate) + 1;
        if ($monthDiff > 13) {
            return response()->json(['error' => 'Date range cannot exceed 12 months'], 422);
        }

        // Fetch cumulative totals from database
        $cumulativeCashSales = $this->cumulativePOSSales();
        $cumulativeCreditSales = $this->cumulativeInvoicePayments();
        $cumulativeCogs = $this->cumulativeCostOfGoodsSold();
        $cumulativeGrossProfit = ($cumulativeCashSales + $cumulativeCreditSales) - $cumulativeCogs;
        $cumulativeExpensesData = $this->cumulativeExpenses();
        $cumulativeTotalExpenses = $cumulativeExpensesData['total_expenses'];
        $cumulativeProfitLoss = $cumulativeGrossProfit - $cumulativeTotalExpenses;

        $result = [
            'months' => array_column($months, 'label'),
            'data' => [
                'cash_sales' => [],
                'credit_sales' => [],
                'cost_of_goods_sold' => [],
                'gross_profit' => [],
                'expenses' => [],
                'profit_loss' => [],
            ],
            'cumulative' => [
                'cash_sales' => $cumulativeCashSales,
                'credit_sales' => $cumulativeCreditSales,
                'cost_of_goods_sold' => $cumulativeCogs,
                'gross_profit' => $cumulativeGrossProfit,
                'expenses' => $cumulativeExpensesData['expenses'],
                'total_expenses' => $cumulativeTotalExpenses,
                'profit_loss' => $cumulativeProfitLoss,
            ],
        ];

        foreach ($months as $index => $month) {
            $monthFrom = $month['start'];
            $monthTo = $month['end'];

            $cashSales = $this->calculatePOSSales($monthFrom, $monthTo);
            $result['data']['cash_sales'][$index] = $cashSales;

            $creditSales = $this->incomeFromInvoicePayments($monthFrom, $monthTo);
            $result['data']['credit_sales'][$index] = $creditSales;

            $cogs = $this->totalCostOfGoodsSold($monthFrom, $monthTo);
            $result['data']['cost_of_goods_sold'][$index] = $cogs;

            $grossProfit = ($cashSales + $creditSales) - $cogs;
            $result['data']['gross_profit'][$index] = $grossProfit;

            $expenses = $this->fetchExpenses($monthFrom, $monthTo);
            $totalExpenses = array_sum(array_column($expenses, 'dr_amount'));
            $result['data']['expenses'][$index] = $expenses;

            $profitLoss = $grossProfit - $totalExpenses;
            $result['data']['profit_loss'][$index] = $profitLoss;
        }

        return response()->json($result);
    }
}