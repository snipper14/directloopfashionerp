<?php

namespace App\Http\Traits;

use App\Http\Controllers\BaseController;
use App\Sale\POSale;
use App\Sale\RoomServiceSale;
use App\Sale\TakeAwaySale;
use Illuminate\Support\Facades\Auth;

trait DailySalesCalculationTrait
{
    public function getDailySalesSummary()
    {



        $res = array(
            'total_sales' => $this->calculatTotal(),
            'dinein_total' => $this->totalDailyDinerSales(),
            'take_away_sales' => $this->totalDailyTakeAwaySaleTotal(),
            'room_service_sales' => $this->totalDailyRSSales()
        );
        return $res;
    }
    private function calculatTotal()
    {
        $sum_sales = 0;
        if ($this->totalDailyDinerSales()->total_sales_amount) {
            $sum_sales += $this->totalDailyDinerSales()->total_sales_amount;
        }
        if ($this->totalDailyTakeAwaySaleTotal()->total_sales_amount) {
            $sum_sales += $this->totalDailyTakeAwaySaleTotal()->total_sales_amount;
        }
        if ($this->totalDailyRSSales()->total_sales_amount) {
            $sum_sales += $this->totalDailyRSSales()->total_sales_amount;
        }
        return $sum_sales;
    }

    public function totalDailyTakeAwaySaleTotal()
    {
        $total_take_away = 0;
        $from = request('from');
        $to = request('to');
        $main_query = TakeAwaySale::where(['cashier_id' => app(BaseController::class)->user_id()]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $takeaway_res =  $main_query->selectRaw('
        SUM(receipt_total) AS total_sales_amount,
        SUM(cash_pay) AS total_cash_pay,
        SUM(mpesa_pay) AS total_mpesa_pay,
        SUM(card_pay) AS total_card_pay
        ')->first();

        return $takeaway_res;
    }



    public function totalDailyDinerSales()
    {
        $totals = 0;
        $from = request('from');
        $to = request('to');
        $main_query = POSale::where(['cashier_id' => app(BaseController::class)->user_id()]); //where(app(BaseController::class)->user_branch_id());
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $takeaway_res =  $main_query->selectRaw('
        SUM(receipt_total) AS total_sales_amount,
        SUM(cash_pay+receipt_balance) AS total_cash_pay,
        SUM(mpesa_pay) AS total_mpesa_pay,
        SUM(card_pay) AS total_card_pay
        ')->first();
        return $takeaway_res;
    }

    public function totalDailyRSSales()
    {
        $totals = 0;
        $from = request('from');
        $to = request('to');
        $main_query = RoomServiceSale::where(['cashier_id' => app(BaseController::class)->user_id()]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $takeaway_res =  $main_query->selectRaw('
        SUM(receipt_total) AS total_sales_amount,
        SUM(cash_pay+receipt_balance) AS total_cash_pay,
        SUM(mpesa_pay) AS total_mpesa_pay,
        SUM(card_pay) AS total_card_pay
        ')->first();

        return $takeaway_res;
    }
}
