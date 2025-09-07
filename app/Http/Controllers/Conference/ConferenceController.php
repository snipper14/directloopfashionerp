<?php

namespace App\Http\Controllers\Conference;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Conference\ConferenceBooking;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Conference\ConferenceRequest;

class ConferenceController extends BaseController
{
    function create(ConferenceRequest $request){
        ConferenceBooking::create($request->validated()+Parent::user_branch_id());
        return response()->json(array("receipt_no"=>$this->receiptNo()));
    }


    function getReceiptNo(){
        return response()->json(array("receipt_no"=>$this->receiptNo()));
    }
    function receiptNo()
    {


        $latest = ConferenceBooking::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $receiptNo = $today . '-' . '' . sprintf('%04d', $string);
        $isUnique = ConferenceBooking::where('receipt_no', $receiptNo)->first();
        if ($isUnique) {
            $receiptNo = $today . '-' . '' . ($latest->id + 1);
        }
        return  $receiptNo . Parent::user_id();
    }

    public function fetch()
    {


        $main_query = ConferenceBooking::with(["user"])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('conference_room', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('company_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')
                    ->orWhere("total_paid", 'LIKE', '%' . request('search') . '%')
                    ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas("user",function($query){
                        $query->where("name","LIKE","%".request('search')."%");
                    });
                   
            });
        });
        $main_query->when(request("curr_date")!="",function($query){
           $query-> whereRaw('DATE(created_at) = ?', [request("curr_date")]);
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
   function  fetchTotals(){
      

    $main_query = ConferenceBooking::with(["user"])->when(request('search', '') != '', function ($query) {
        $query->where(function ($q) {

            $q->where('conference_room', 'LIKE', '%' . request('search') . '%')
                ->orWhere('company_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                ->orWhere('phone', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('details', 'LIKE', '%' . request('search') . '%')
                ->orWhere("total_paid", 'LIKE', '%' . request('search') . '%')
                ->orWhereHas("user",function($query){
                    $query->where("name","LIKE","%".request('search')."%");
                });
               
        });
    });
    $main_query->when(request("curr_date")!="",function($query){
        $query-> whereRaw('DATE(created_at) = ?', [request("curr_date")]);
     });
    if (!Parent::checkForPermission()) {
        $main_query->where('user_id', Parent::user_id());
    }
    $res = $main_query->selectRaw('SUM(total_paid) AS sum_total_paid')->first();
    
    return response()->json($res); 
   }
}
