<?php

namespace App\Http\Controllers\ReceptionBalance;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\ReceptionBalanceRequest;
use App\Http\Requests\Reception\ReceptionClosingBalanceRequest;
use App\Reception\ReceptionBalance;
use App\Reservation\RoomReservation;
use App\Reservation\RoomSale;
use App\Rules\ValidateReceptionBalance;
use App\Rules\ValidateReceptionClosingBalance;
use Illuminate\Http\Request;

class ReceptionBalanceController extends BaseController
{
    public function createOpeningBalance(ReceptionBalanceRequest $request)
    {
        $this->validate($request, ['opening_amount' => new ValidateReceptionBalance()]);
        ReceptionBalance::create($request->validated() + Parent::user_branch_id());
        return true;
    }

    public function showTotal()
    {
        $from = request('from');
        $to = request('to');
        $cash_total = 0;
        $main_query_cashsale = RoomReservation::where(Parent::user_branch_id());
        $main_query_cashsale->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $cashsale_result = $main_query_cashsale->selectRaw('
        SUM(total) AS total_amount,
        SUM(amount_paid) AS total_paid,
        
        SUM(cash_paid) AS total_cash_paid,
        SUM(mobile_money_paid) AS total_mobile_money_paid,
        SUM(online_payment) AS total_online_payment,
        SUM(card_paid) AS total_card_paid,
        SUM(cheque_paid) AS total_cheque_paid,
        SUM(bank_transfer_paid) AS total_bank_transfer_paid
        ')->first();
        return response()->json($cashsale_result);
    }

    function getOpeningTime(Request $request)
    {

        $this->validate($request, ['closing_amount' => new ValidateReceptionClosingBalance()]);
        $res = ReceptionBalance::where(Parent::user_branch_id() + ["pending_closing" => "1"])->orderBy('id', 'DESC')->first();

        return response()->json($res);
    }

    function createClosingBalance(ReceptionClosingBalanceRequest $request)
    {
       // $this->validate($request, ['closing_amount' => new ValidateReceptionClosingBalance()]);
        ReceptionBalance::create($request->validated()+Parent::user_branch_id());
        return true;
    }

    function fetch()
    {
        $from = request('opening_time');
        $to = request('closing_time');
        $main_query = ReceptionBalance::with(['user'])->where(Parent::branch_array());
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
        $main_query = ReceptionBalance::with(['user'])->where(Parent::branch_array());
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

        $res = $main_query->selectRaw('
        SUM(opening_amount) AS total_opening_amount,
        SUM(closing_amount) AS total_closing_amount,
        SUM(sold_amount) AS total_sold_amount,
        SUM(total_amount) AS total_total_amount,
        SUM(cash_amount) AS total_cash_amount,
        SUM(bank_transfer_amount) AS total_bank_transfer_amount,
        SUM(mobile_money) AS total_mobile_money,
        SUM(online_paid) AS total_online_paid,
        SUM(credit_card) AS total_credit_card,
        SUM(cheque) AS total_cheque
        
        ')->first();


        return response()->json($res);
    }

    function destroy(Request $request)
    {
        ReceptionBalance::where("id", $request->route('id'))->delete();
        return true;
    }




    
    function closeShiftSales()
    {
        $check_lastshift_closing_time = $this->checkLastShifClosingTime();

        $res = array(

            'sales_summary' => $this->calculateSalesFromLastShift($check_lastshift_closing_time),
            'closing_time' => Parent::currentDateTime(),
            'opening_time' => $check_lastshift_closing_time

        );
        return response()->json($res);
    }
    function checkLastShifClosingTime()
    {
        $res = ReceptionBalance::where(['user_id' => Parent::user_id()])->orderBy('id', 'DESC')->first();
        if ($res) {
            return $res->closing_time;
        } else {
            return '';
        }
    }
    function calculateSalesFromLastShift($from)
    {

        $to = Parent::currentDateTime();
        $main_query = RoomSale::where(['user_id' => Parent::user_id()]);


        $main_query->when($from !== '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });

        $res =  $main_query->selectRaw('
       
        SUM(amount_paid) AS total_paid,
        
        SUM(cash_paid) AS total_cash_paid,
        SUM(mobile_money_paid) AS total_mobile_money_paid,
        SUM(online_paid) AS total_online_paid,
        SUM(card_paid) AS total_card_paid,
        SUM(cheque_paid) AS total_cheque_paid,
        SUM(bank_transfer_paid) AS total_bank_transfer_paid
        ')->first();

        return $res;
    }


    function fetchBalancesDetails(Request $request)
    {
        $opening_time = $request->opening_time ? $request->opening_time : '2000-01-01 00:00:00';
        $closing_time = $request->closing_time;
        $cashier_id = $request->user_id;

        $main_query = RoomReservation::with(['guest_company', 
          'house_keeper', 'waiter', 'guest', 'branch', 'user',
        'room.room_standard', 'room_package'])->where(Parent::branch_array());

        $main_query->whereBetween('created_at', [$opening_time, $closing_time]);

        $main_query->where(['user_id' => $cashier_id]);
        $data = $main_query->latest('created_at')->get();

        return response()->json($data);
    }
}
