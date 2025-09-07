<?php

namespace App\Http\Controllers\Reports;

use App\Conference\ConferenceBooking;
use Illuminate\Http\Request;
use App\Sale\AllSalesTotalReport;
use App\POS\OrderEmployeeDeduction;
use App\Http\Controllers\Controller;
use App\Reservation\RoomReservation;
use App\Http\Controllers\BaseController;
use App\Invoices\Invoice;
use App\POS\POSCredit;
use App\PurchaseOrder\DirectPOReceivable;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\UserLogin;

class AllReportsController extends BaseController
{
    function fetch(){
      $data=array(
          "all_sales"=>$this->fetchAllSaleTotal(),
          'refundable'=>$this->fetchEmployeeRefundable(),
        
          'all_direct_purchases'=>$this->fetchDirectPOReceivableTotals(),
          'all_purchases'=>$this->fetchPOReceivableTotals(),
        
             'invoices'=>$this->fetchInvoice(),
             'pos_credit'=>$this->fetchPOSCredit()
      );
      return response()->json($data);
    }

    function fetchAllSaleTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSalesTotalReport::when(request('from', '') != '' && request('to', '') != '',
         function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
      
       
        $main_query->selectRaw('
    SUM(receipt_total) AS sum_receipt_total,
    SUM(cash_pay+receipt_balance) AS sum_cash_pay,
    SUM(mpesa_pay) AS sum_mpesa_pay,
    SUM(card_pay) AS sum_card_pay,
    SUM(total_vat) AS sum_total_vat
    ');
        $res = $main_query->first();

        return $res;
    }



    function fetchEmployeeRefundable()
    {
        $main_query = OrderEmployeeDeduction::where('clear_status','!=', 'pending');

        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->selectRaw("SUM(row_total) AS sum_row_total");
        $res1 = $main_query->first();

        if ($res1) {
          
            return $res1;
           
        } else {
            return 0;
        }
    }

  

    public function fetchDirectPOReceivableTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query=DirectPOReceivable::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $raw_query->selectRaw('SUM(sub_total) AS sum_total');
        $res =   $raw_query->where(Parent::branch_array())->first();

        return $res;
    }


    public function fetchPOReceivableTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query=PurchaseOrderReceivable::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $raw_query->selectRaw('SUM(sub_total) AS sum_total');
        $res =   $raw_query->where(Parent::branch_array())->first();

        return $res;
    }


 
public function fetchInvoice(){
    $from = date(request('from'));
    $to = date(request('to'));
    $raw_query=Invoice::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

        $query->whereBetween('created_at', [$from, $to]);
    });
    $raw_query->selectRaw('SUM(row_total) AS sum_row_total');
    $res =   $raw_query->first();

    return $res;
}
function fetchPOSCredit(){
    $from = date(request('from'));
    $to = date(request('to'));
    $raw_query=POSCredit::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

        $query->whereBetween('created_at', [$from, $to]);
    });
    $raw_query->selectRaw('SUM(credit_amount) AS sum_credit_amount');
    $res =   $raw_query->first();

    return $res;
}

    function fetchLogins(){
       
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query=UserLogin::with(['user'])->latest('created_at');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
              

                    $q->whereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                  
            });
        });
        return response()->json($raw_query->paginate(50));
    }
}
