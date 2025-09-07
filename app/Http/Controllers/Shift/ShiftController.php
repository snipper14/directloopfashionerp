<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\ShiftRequest;
use App\Shift\AttendanceShift;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    public function create(ShiftRequest $request){

        $status=$request->status;
        if($status=="shift_start"){
        AttendanceShift::create(['status'=>'started']+Parent::user_branch_id()+['shift_start'=>Parent::currentDateTime()]);
        }else{
            AttendanceShift::where(['status'=>'started']+Parent::user_branch_id())
            ->orderBy('id','DESC')->take(1)->update(['end_shift'=>Parent::currentDateTime(),'status'=>'end']);
        }
        $res=AttendanceShift::where(Parent::user_branch_id())->orderBy('id','DESC')->first();
        if($res){
            return response()->json($res->status);
        }
        return response()->json('end');

    }

    public function fetch(){
        $res=AttendanceShift::where(Parent::user_branch_id())->orderBy('id','DESC')->first();
        if($res){
            return response()->json($res->status);
        }
        return response()->json('end');
    }
}
