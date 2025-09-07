<?php

namespace App\Http\Controllers\Employee;

use App\Employee\LeaveType;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveTypeController extends BaseController
{
    function create(Request $request){
        $request->validate([
            'leave_type' => 'required',
           'leave_count'=>'required|gte:1',
           'interval'=>"required"

        ]);
        LeaveType::firstOrCreate(['leave_type'=>$request->leave_type],
        ['leave_count'=>$request->leave_count,'interval'=>$request->interval]);
        $res=LeaveType::get();
        return $res;
    }
    function fetch(){
        $res=LeaveType::get();
        return $res;
    }
    function destroy(Request $request){
        $res=LeaveType::where('id',$request->route('id'))->delete();
        return $res;
    }
}
