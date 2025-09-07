<?php

namespace App\Http\Controllers\TableBooking;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableBookingRequest;
use App\TableBooking\TableBooking;
use Illuminate\Http\Request;

class TableBookingController extends BaseController
{
    function create(TableBookingRequest $request){
        $res=TableBooking::create($request->validated());
        return true;
    }

    function fetch(){
        $from=request('from');
        $to=request('to');
        $main_query=TableBooking::when(request('search')!='',function($q){
           $q->where('name','LIKE', '%' . request('search') . '%')
           ->orWhere('email','LIKE','%' . request('search') . '%')
           ->orWhere('phone','LIKE','%' . request('search') . '%');
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('event_date', [$from, $to]);
        });


        $main_query->latest();
        $res=$main_query->get();
        if(request('page')>0){
            $res=$main_query->paginate(20);
        }
        return response()->json($res);
    }
}
