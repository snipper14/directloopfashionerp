<?php

namespace App\Http\Controllers\Cashier;

use App\Sale\POSale;
use App\Sale\TakeAwaySale;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Cashier\CashierBalance;
use App\Payments\CustomerPayment;
use App\Sale\AllSalesTotalReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Conference\ConferenceBooking;
use App\Expenses\ExpensePayment;
use App\Expenses\Expenses;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateCashierClosingBalance;
use App\Rules\ValidateCashierOpeningBalance;
use App\Http\Traits\DailySalesCalculationTrait;
use App\Http\Requests\Cashier\CashierClosingBalanceRequest;
use App\Http\Requests\Cashier\CashierOpeningBalanceRequest;

class CashierBalanceController extends BaseController
{
    use DailySalesCalculationTrait;


    function closeShift(CashierClosingBalanceRequest $request)
    {
        DB::transaction(function () use ($request) {

            CashierBalance::create($request->validated() +  Parent::user_branch_id());
            $account_id = Auth::user()->branch->account_id;
         

            if ($account_id) {

                // $res = LedgerAccount::where('id', $account_id)->first();
                // GeneralLedger::create([
                //     'other_account_name' => "Cashier Closing Balance",
                //     'account_id' => $account_id,
                //     'entry_code' => 'Balance',
                //     'account_type' => $res->account,
                //     'debit_amount' => $request->cash_collected,
                //     'credit_amount' => 0,
                //     'details' => "Cashier Closing Balance",
                //     'ref' => 'B',
                //     'entry_date' => date("Y-m-d")


                // ] + parent::user_branch_id());
            }
           
        }, 5);
        return true;
    }



    function fetch()
    {
        $from = request('opening_time');
        $to = request('closing_time');
        $main_query = CashierBalance::with(['user'])->where(Parent::branch_array());
        $main_query->when(request('opening_time', '') != '' && request('closing_time', '') != "", function ($query) use ($from, $to) {
            $query->whereBetween('closing_time', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($q) {
            $q->where('details', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->paginate('20');
        }
        return response()->json($res);
    }

    function fetchSum()
    {
        $from = request('opening_time');
        $to = request('closing_time');
        $main_query = CashierBalance::with(['user'])->where(Parent::branch_array());
        $main_query->when(request('opening_time', '') != '' && request('closing_time', '') != "", function ($query) use ($from, $to) {
            $query->whereBetween('closing_time', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($q) {
            $q->where('details', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }

        $res = $main_query->selectRaw('SUM(opening_amount) AS total_opening_amount,SUM(closing_amount) AS total_closing_amount')->first();


        return response()->json($res);
    }

    function destroy(Request $request)
    {
        CashierBalance::where("id", $request->route('id'))->delete();
        return true;
    }


    function closeShiftSales()
    {
        $check_lastshift_closing_time = $this->checkLastShifClosingTime();

        $res = array(
            'expense_payments'=>$this->calculateExpensesPaymentsFromLastShift($check_lastshift_closing_time),
            "invoice_payments" => $this->calculateInvoicePaymentsFromLastShift($check_lastshift_closing_time),
            'sales_summary' => $this->calculateSalesFromLastShift($check_lastshift_closing_time),
            'conference_sales' => $this->calculateConferenceSale($check_lastshift_closing_time),
            'closing_time' => Parent::currentDateTime(),

            'opening_time' => $check_lastshift_closing_time

        );
        return response()->json($res);
    }
    function checkLastShifClosingTime()
    {
        $res = CashierBalance::where(['user_id' => Parent::user_id()])->orderBy('id', 'DESC')->first();
        if ($res) {
            return $res->closing_time;
        } else {
            return '';
        }
    }
    function calculateSalesFromLastShift($from)
    {

        $to = Parent::currentDateTime();
        $main_query = AllSalesTotalReport::where(['cashier_id' => Parent::user_id()]);


        $main_query->when($from !== '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });

        $res =  $main_query->selectRaw('
        SUM(receipt_total) AS total_sales_amount,
        SUM(cash_pay+receipt_balance) AS total_cash_pay,
        SUM(mpesa_pay) AS total_mpesa_pay,
        SUM(card_pay) AS total_card_pay
        ')->first();

        return $res;
    }
    function calculateInvoicePaymentsFromLastShift($from)
    {

        $to = Parent::currentDateTime();
        $main_query = CustomerPayment::where(['user_id' => Parent::user_id(),'pay_method'=>'Cash']);


        $main_query->when($from !== '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });

        $res =  $main_query->selectRaw('
        SUM(amount_paid) AS total_amount_paid
       ')->first();
        $total = 0;
        if ($res->total_amount_paid) {
            $total = $res->total_amount_paid;
        }
        return $total;
    }
    function calculateExpensesPaymentsFromLastShift($from)
    {

        $to = Parent::currentDateTime();
        $main_query = ExpensePayment::where(['user_id' => Parent::user_id(),'pay_method'=>'Cash']);


        $main_query->when($from !== '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });

        $res =  $main_query->selectRaw('
        SUM(amount_paid) AS total_amount_paid
       ')->first();
        $total = 0;
        if ($res->total_amount_paid) {
            $total = $res->total_amount_paid;
        }
        return $total;
    }
    function calculateConferenceSale($from)
    {

        $to = Parent::currentDateTime();
        $main_query = ConferenceBooking::where(['user_id' => Parent::user_id()]);


        $main_query->when($from !== '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });

        $res =  $main_query->selectRaw('
        SUM(total_paid) AS total_paid_amount
        ')->first();
        $data = 0;
        if ($res) {
            $data = $res->total_paid_amount ? $res->total_paid_amount : 0;
        }

        return $data;
    }
    function fetchBalancesDetails(Request $request)
    {
        $opening_time = $request->opening_time ? $request->opening_time : '2000-01-01 00:00:00';
        $closing_time = $request->closing_time;
        $cashier_id = $request->user_id;

        $main_query = AllSalesTotalReport::with(['user', 'cashier', 'waiter'])->where(Parent::branch_array());

        $main_query->whereBetween('created_at', [$opening_time, $closing_time]);

        $main_query->where(['cashier_id' => $cashier_id]);
        $data = $main_query->latest('created_at')->get();

        return response()->json($data);
    }
}
