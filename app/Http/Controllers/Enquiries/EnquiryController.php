<?php

namespace App\Http\Controllers\Enquiries;

use App\Enquiries\Enquiry;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enquiries\EnquiryRequest;
use Illuminate\Http\Request;

class EnquiryController extends BaseController
{
    public function create( EnquiryRequest $request){
        Enquiry::create($request->validated());
        return true;

    }

    function fetch(){
        $from=request('from');
        $to=request('to');
        $main_query=Enquiry::when(request('search')!='',function($q){
           $q->where('name','LIKE', '%' . request('search') . '%')
           ->orWhere('email','LIKE','%' . request('search') . '%')
           ->orWhere('subject','LIKE','%' . request('search') . '%');
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });


        $main_query->latest();
        $res=$main_query->get();
        if(request('page')>0){
            $res=$main_query->paginate(20);
        }
        return response()->json($res);
    }
}
