<?php

namespace App\Http\Controllers\GeneralLedger;

use App\Invoices\Invoice;
use App\Expenses\Expenses;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Expenses\ExpenseLedger;
use App\Sale\AllSalesTotalReport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;

class TrialBalanceController extends BaseController
{
    function fetch()
    {

        // $data =$this->fetchExpenses()+ [$this->accountDrPayable(), $this->accountPayable(), $this->accountReceivableDr(), $this->accountInvoiceSales(),];
        $data = $this->fetchExpenses() + [
            // $this->accountPayableDr(), $this->accountPayableCr(),
           // $this->accountReceivableDr(), $this->accountReceivableCr()

        ];

        $data2 = array_merge($data, $this->fetchledgerAccounts());
        $data3 = array_merge($data2, $this->payableAccount());
        $data4 = array_merge($data3, $this->receivableAccount());
        return response()->json($data4);
    }
    function accountPayableCr()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CreditLedger::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('
        SUM(credit) AS total_credit')->first();
        $total_cr = 0;
        if ($res->total_credit) {
            $total_cr = $res->total_credit;
        }
        $data = array('type' => 'cr', 'account' => "Account Payable", 'dr_amount' => 0, 'cr_amount' => $total_cr);
        return $data;
    }
    function accountPayableDr()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CreditLedger::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('
        SUM(debit) AS total_debit')->first();
        $total_dr = 0;
        if ($res->total_debit) {
            $total_dr = $res->total_debit;
        }
        $data = array('type' => 'dr', 'account' => "Account Payable", 'dr_amount' => $total_dr, 'cr_amount' => 0);
        return $data;
    }
    function payableAccount()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CreditLedger::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('SUM(debit) AS total_debit,SUM(credit) AS total_credit');
        $res = $main_query->get();
        $data = [];
        foreach ($res as  $value) {
            $dr_amount = $value['total_debit'] - $value['total_credit'];

            if ($dr_amount > 0) {
                $data[] = array('type' => 'dr', 'account' => "Payable Accounts", 'dr_amount' => $dr_amount, 'cr_amount' => 0);
            }
            $cr_amount = $value['total_credit'] - $value['total_debit'];

            if ($cr_amount > 0) {

                $data[] = array('type' => 'cr', 'account' => "Payable Accounts", 'dr_amount' => 0, 'cr_amount' => $cr_amount);
            }
        }

        return $data;
    }
    function receivableAccount()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CustomerLedger::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('SUM(debit) AS total_debit,SUM(credit) AS total_credit');
        $res = $main_query->get();
        $data = [];
        foreach ($res as  $value) {
            $dr_amount = $value['total_debit'] - $value['total_credit'];

            if ($dr_amount > 0) {
                $data[] = array('type' => 'dr', 'account' => "Receivable Accounts", 'dr_amount' => $dr_amount, 'cr_amount' => 0);
            }
            $cr_amount = $value['total_credit'] - $value['total_debit'];

            if ($cr_amount > 0) {

                $data[] = array('type' => 'cr', 'account' => "Receivable Accounts", 'dr_amount' => 0, 'cr_amount' => $cr_amount);
            }
        }

        return $data;
    }
    public function fetchledgerAccounts()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  GeneralLedger::withoutGlobalScope(BranchScope::class)->with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->selectRaw('*,SUM(debit_amount) AS total_debit_amount,SUM(credit_amount) AS total_credit_amount')->groupBy('account_id');
        $res = $main_query->get();
        $data = [];
        foreach ($res as  $value) {
            $dr_amount = $value['total_debit_amount'] - $value['total_credit_amount'];
            if ($dr_amount > 0) {
                $data[] = array('type' => 'dr', 'account' => $value['account']['account'], 'dr_amount' => $dr_amount, 'cr_amount' => 0);
            }
            $cr_amount = $value['total_credit_amount'] - $value['total_debit_amount'];
            if ($cr_amount > 0) {

                $data[] = array('type' => 'cr', 'account' => $value['account']['account'], 'dr_amount' => 0, 'cr_amount' => $cr_amount);
            }
        }

        return $data;
    }

    function fetchAssets()
    {
        $data = [$this->accountDrPayable(),  $this->accountReceivableDr()];
        $data1 = array_merge($data, $this->drledger());
        return response()->json($data1);
    }

    function fetchLiabilities()
    {
        $data = [$this->accountPayable()];
        $data1 = array_merge($data, $this->crledger());
        return response()->json($data1);
    }

    function accountPayable()
    {
        $from = date(request('from'));
        $to = date(request('to'));


        $main_query = CreditLedger::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        );
        $res = $main_query->selectRaw('
        SUM(credit-debit) AS actual_balance,SUM(debit) AS total_debit')->first();
        $data = array('type' => 'cr', 'account' => "Account Payable", 'dr_amount' => $res->total_debit, 'cr_amount' => $res->actual_balance);
        return $data;
    }

    function accountDrPayable()
    {




        $main_query = Inventory::join('stocks', 'inventories.stock_id', '=', 'stocks.id');


        $main_query->selectRaw('SUM(inventories.qty) AS sum_qty,SUM(stocks.selling_price*inventories.qty) AS value_on_sp,SUM(stocks.purchase_price*inventories.qty) AS value_on_pp');
        $data = $main_query->first();






        $data = array('type' => 'dr', 'account' => "Inventory", 'dr_amount' => $data->value_on_pp, 'cr_amount' => 0);
        return $data;
    }

    function fetchExpenses()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Expenses::withoutGlobalScope(BranchScope::class)->with(['expense_type.ledgerAccount'])->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('date_recorded', [$from, $to]);
            }
        )
            ->selectRaw('*,SUM(amount) AS sum_expenses')->groupBy('expense_type_id');

        $res = $raw_query->get();
        $data = [];
        foreach ($res as  $value) {
            $data[] = array('type' => 'dr', 'account' => $value['expense_type']['ledgerAccount']['account'], 'dr_amount' => $value->sum_expenses, 'cr_amount' => 0);
        }

        return $data;
    }

    function accountDrExpenses()
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



        $data = array('type' => 'dr', 'account' => "Expenses", 'dr_amount' => $res->sum_expenses, 'cr_amount' => 0);
        return $data;
    }

    public function accountReceivableDr()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  CustomerLedger::withoutGlobalScope(BranchScope::class)->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        )
            ->selectRaw('SUM(debit) AS total_balance')

            ->orderBy('id', 'ASC');
        $res = $main_query->first();
        $total = 0;
        if ($res->total_balance) {
            $total = $res->total_balance;
        }
        $data = array('type' => 'dr', 'account' => "Account Receivable", 'dr_amount' => $total, 'cr_amount' => 0);
        return $data;
    }
    public function accountReceivableCr()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  CustomerLedger::withoutGlobalScope(BranchScope::class)->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('entry_date', [$from, $to]);
            }
        )
            ->selectRaw('SUM(credit) AS total_balance')

            ->orderBy('id', 'ASC');
        $res = $main_query->first();
        $total = 0;
        if ($res->total_balance) {
            $total = $res->total_balance;
        }
        $data = array('type' => 'dr', 'account' => "Account Receivable", 'dr_amount' => 0, 'cr_amount' => $total);
        return $data;
    }




    public function drledger()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query =  GeneralLedger::withoutGlobalScope(BranchScope::class)->with(['account.chart_account'])
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

        $main_query =  GeneralLedger::withoutGlobalScope(BranchScope::class)->with(['account.chart_account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->selectRaw('*,SUM(debit_amount) AS dr_amount')
            ->where('debit_amount', '>', 0)->where('account_id', $value->account_id);


        $res = $main_query->first();

        $data = array('type' => 'dr', 'account' => $res->account->account . '(' . $res->account['chart_account']->name . ')', 'dr_amount' => $res->dr_amount, 'cr_amount' => 0);
        return $data;
    }


    public function crledger()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query =  GeneralLedger::withoutGlobalScope(BranchScope::class)->with(['account'])
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )

            ->where('credit_amount', '>', 0)->groupBy('account_id')
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

        $main_query =  GeneralLedger::withoutGlobalScope(BranchScope::class)->with(['account'])
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
}
