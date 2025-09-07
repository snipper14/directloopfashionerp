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
use Illuminate\Database\Console\Migrations\BaseCommand;

class ProfitLossController extends BaseController
{
    function fetchAssets()
    {
        $data = [$this->accountDrInvoiceSales(), $this->fetchDrSupplierCreditNote()];
        // $data1 = array_merge($data, $this->drledger());
        return response()->json($data);
    }

    function fetchLiabilities()
    {
        $data = $this->fetchExpenses();
        //   $data1 = array_merge($data, $this->crledger());
        return response()->json($data);
    }

function fetchExpenses()
{
    $from = request('from') ? date(request('from')) : null;
    $to   = request('to')   ? date(request('to'))   : null;

    // 1) From GENERAL LEDGER (all expense-category accounts)
    $glRows = GeneralLedger::query()
        ->join('ledger_accounts', 'general_ledgers.account_id', '=', 'ledger_accounts.id')
        ->where('ledger_accounts.account_type', 'expenses')
        ->when($from && $to, function ($q) use ($from, $to) {
            $q->whereBetween('general_ledgers.entry_date', [$from, $to]);
        })
        ->selectRaw("
            ledger_accounts.id   as ledger_account_id,
            ledger_accounts.account as ledger_account_name,
            ledger_accounts.account_no as ledger_account_no,
            COALESCE(SUM(general_ledgers.debit_amount - general_ledgers.credit_amount), 0) as sum_expenses
        ")
        ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
        ->get();

    // 2) From EXPENSES table (map via expense_types → ledger_accounts)
    $expRows = Expenses::withoutGlobalScope(\App\Scopes\BranchScope::class)
        ->join('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id')
        ->join('ledger_accounts', 'expense_types.ledger_account_id', '=', 'ledger_accounts.id')
        ->when($from && $to, function ($q) use ($from, $to) {
            $q->whereBetween('expenses.date_recorded', [$from, $to]);
        })
        ->selectRaw("
            ledger_accounts.id   as ledger_account_id,
            ledger_accounts.account as ledger_account_name,
            ledger_accounts.account_no as ledger_account_no,
            COALESCE(SUM(expenses.amount), 0) as sum_expenses
        ")
        ->groupBy('ledger_accounts.id', 'ledger_accounts.account', 'ledger_accounts.account_no')
        ->get();

    // 3) Merge totals by ledger_account_id
    $totals = [];

    $adder = function ($row) use (&$totals) {
        $id   = $row->ledger_account_id;
        if (!isset($totals[$id])) {
            $totals[$id] = [
                'ledger_account_id'   => $id,
                'ledger_account_name' => $row->ledger_account_name,
                'ledger_account_no'   => $row->ledger_account_no,
                'sum_expenses'        => 0.0,
            ];
        }
        $totals[$id]['sum_expenses'] += (float)$row->sum_expenses;
    };

    foreach ($glRows as $r)  $adder($r);
    foreach ($expRows as $r) $adder($r);

    // 4) Same right-pad rule for the first segment of account_no ("1" -> "100")
    $formatAccountNo = function (?string $value) {
        if (!$value) return $value;
        $parts = explode('-', $value);
        if (isset($parts[0]) && strlen($parts[0]) === 1) {
            $parts[0] .= '00'; // pad RIGHT to 3 chars
        }
        return implode('-', $parts);
    };

    // 5) Build final payload
    $data = [];
    foreach ($totals as $t) {
        $prettyNo = $formatAccountNo($t['ledger_account_no']);
        $data[] = [
            'type'      => 'dr',
            'account'   => "{$prettyNo} . {$t['ledger_account_name']}",
            'dr_amount' => $t['sum_expenses'],
            'cr_amount' => 0.0,
        ];
    }

    return $data;
}

  
function formatAccountNo($value){
     $parts = explode('-', $value->ledger_account_no);

    // If first part is only 1 digit, append '00'
    if (isset($parts[0]) && strlen($parts[0]) === 1) {
        $parts[0] .= '00';
    }

    // Join back into final account format
    $formatted_account_no = implode('-', $parts);
   return $formatted_account_no;
}
   
    function profitLossAmount()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)->with(['expense_type'])->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('date_recorded', [$from, $to]);
            }
        )
            ->selectRaw('SUM(amount) AS sum_expenses');

        $res = $raw_query->first();
        $total_expenses = 0;
        if ($res) {
            $total_expenses += $res->sum_expenses;
        }
        $profit_loss = ($this->totalSales() + $this->incomeFromInvoicePayments()) -
            ($this->totalcostOfGoodSold() + $total_expenses);
        return $profit_loss;
    }
    function fetchCrCustomerCreditNote()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CreditNote::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('credit_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('
        SUM(line_total) AS actual_balance')->first();
        $data = array('type' => 'cr', 'account' => "Customer Credit Notes", 'dr_amount' => 0, 'cr_amount' => $res->actual_balance);
        return $data;
    }
    function fetchDrSupplierCreditNote()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = SupplierCreditNote::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('credit_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('
        SUM(credit_amount) AS actual_balance')->first();
        $data = array('type' => 'dr', 'account' => "Supplier Credit Notes", 'dr_amount' => $res->actual_balance, 'cr_amount' => 0);
        return $data;
    }


    function accountCrSupplierInvoices()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = SupplierInvoice::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        )
            ->selectRaw('SUM(invoice_total) AS sum_invoice');

        $res = $raw_query->first();



        $data = array('type' => 'cr', 'account' => "Product/Assets/Service Aquisitions", 'dr_amount' => 0, 'cr_amount' => $res->sum_invoice);
        return $data;
    }

    function accountCrExpenses()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('date_recorded', [$from, $to]);
            }
        )
            ->selectRaw('SUM(amount) AS sum_expenses');

        $res = $raw_query->first();



        $data = array('type' => 'cr', 'account' => "Expenses", 'dr_amount' => 0, 'cr_amount' => $res->sum_expenses);
        return $data;
    }


    public function accountDrInvoiceSales()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Invoice::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        )

            ->selectRaw('sum(row_total) as invoice_total');



        $res = $raw_query->first();


        // $data = array('type' => 'dr', 'account' => "Sales", 'dr_amount' => ($res->invoice_total + $this->calculatePOSSales()), 'cr_amount' => 0);

        $data = array('type' => 'dr', 'account' => "Sales", 'dr_amount' => ($this->calculatePOSSales()), 'cr_amount' => 0);
        return $data;
    }
    function totalSales()
    {
        $total = $this->calculatePOSSales(); //+ $this->incomeFromInvoicePayments();
        return $total;
    }
    function incomeFromInvoicePayments()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $row_query = Invoice::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('created_at', [$from, $to]);
            }
        );
        $data = $row_query->selectRaw('SUM(row_total) AS sum_total')->first();
        $total = 0;
        if ($data->sum_total) {
            $total = $data->sum_total;
        }

        return $total;
    }
    function totalcostOfGoodSold()
    {
        $total = $this->invoiceCostOfGoodSold() + $this->cashSaleCostOfGoodSold();
        return $total;
    }
    function invoiceCostOfGoodSold()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Invoice::withoutGlobalScope(BranchScope::class)->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        )

            ->selectRaw('sum(qty*purchase_price) as invoice_cost_of_goods_sold');



        $res = $raw_query->first();



        $total = 0;
        if ($res) {
            $total += $res->invoice_cost_of_goods_sold;
        }
        return $total;
    }
    function cashSaleCostOfGoodSold()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });


        $main_query->selectRaw('SUM(cost_price*qty) AS cash_cost_of_good');
        $res = $main_query->first();
        $total = 0;
        if ($res) {
            $total = $res->cash_cost_of_good;
        }
        return $total;
    }
    public function inventoryCostValue()
    {




        $main_query = Inventory::withoutGlobalScope(BranchScope::class)->join('stocks', 'inventories.stock_id', '=', 'stocks.id');


        $main_query->selectRaw('SUM(inventories.qty) AS sum_qty,SUM(stocks.selling_price*inventories.qty) AS value_on_sp,SUM(stocks.purchase_price*inventories.qty) AS value_on_pp');
        $data = $main_query->first();
        $inventory_cost = 0;

        if ($data) {
            $inventory_cost = $data->value_on_pp;
            return response()->json($inventory_cost);
        }
    }

    function calculatePOSSales()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

           // $query->whereBetween('created_at', [$from, $to]);
        });


        $main_query->selectRaw('SUM(receipt_total) AS sum_receipt_total');
        $res = $main_query->first();
        $total = 0;
        if ($res->sum_receipt_total) {
            $total += $res->sum_receipt_total;
        }
        return $total;
    }



    public function drledger()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query =  GeneralLedger::with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            // ->selectRaw('*,SUM(debit_amount-credit_amount) AS dr_amount')
            ->where('debit_amount', '>', 0)->groupBy('account_id')
            ->orderBy('id', 'ASC');
        $res_data = $main_query->get();
        $data = [];

        foreach ($res_data as  $value) {



            $data[] = $this->ledgerDrAccounts($value);
        }
        return $data;
    }
    public function ledgerDrAccounts($value)
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  GeneralLedger::with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->selectRaw('*,SUM(debit_amount) AS dr_amount')
            ->where('debit_amount', '>', 0)->where('account_id', $value->account_id);


        $res = $main_query->first();

        $data = array('type' => 'dr', 'account' => $res->account->account, 'dr_amount' => $res->dr_amount, 'cr_amount' => 0);
        return $data;
    }


    public function crledger()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query =  GeneralLedger::with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )

            ->where('debit_amount', '>', 0)->groupBy('account_id')
            ->orderBy('id', 'ASC');
        $res_data = $main_query->get();
        $data = [];

        foreach ($res_data as  $value) {



            $data[] = $this->ledgerCrAccounts($value);
        }
        return $data;
    }
    public function ledgerCrAccounts($value)
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  GeneralLedger::with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->selectRaw('*,SUM(credit_amount) AS cr_amount')
            ->where('credit_amount', '>', 0)->where('account_id', $value->account_id);


        $res = $main_query->first();
        $data = null;
        if ($res) {
            $data = array('type' => 'dr', 'account' => $res->account->account, 'dr_amount' => 0, 'cr_amount' => $res->cr_amount);
        }
        return $data;
    }
    public function taxAmount()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Invoice::withoutGlobalScope(BranchScope::class)->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        )

            ->selectRaw('sum(tax_amount) as total_tax');



        $res = $raw_query->first();



        $data = array('type' => 'cr', 'account' => "Total Tax", 'dr_amount' => 0, 'cr_amount' => ($res->total_tax + $this->calculatePTotalTax()));
        return $data;
    }
    function calculatePTotalTax()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSalesTotalReport::withoutGlobalScope(BranchScope::class)->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });


        $main_query->selectRaw('SUM(total_vat) AS sum_vat');
        $res = $main_query->first();
        $total = 0;
        if ($res) {
            $total += $res->sum_vat;
        }
        return $total;
    }
    public function totalPayroll()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Payroll::when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('payroll_to', [$from, $to]);
            }
        )

            ->selectRaw('sum(basic_salary) as total_basic_salary');



        $res = $raw_query->firstOrFail()->total_basic_salary;

        $total = 0;
        if ($res) {

            $total = $res;
        }
        return $total;
    }

    function totalExpenses()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)
            ->with(['expense_type'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {
                    $query->whereBetween('date_recorded', [$from, $to]);
                }
            )
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
        $total_expenses = (float) ($this->totalExpenses() ?? 0);
        $total_cogs = (float) ($this->totalcostOfGoodSold() ?? 0);
        $invoice_income = (float) ($this->incomeFromInvoicePayments() ?? 0);

        $profit_loss = ($total_sales + $invoice_income) - ($total_cogs + $total_expenses);

        return response()->json($profit_loss);
    }
}
