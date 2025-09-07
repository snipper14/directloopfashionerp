<?php

namespace App\Http\Controllers\EmployeeType;

use Illuminate\Http\Request;
use App\Employee\EmployeeType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Employee\EmployeeTypeRequest;

class EmployeeTypeController extends BaseController
{
   
    public function create(EmployeeTypeRequest $request){

        $type=EmployeeType::create($request->validated()+Parent::user_branch_id());
        return response()->json(["results"=>$type]);
    }

    public function fetch(){
        $type=EmployeeType::orderBy('id','DESC')->get();
        return response()->json(['results'=>$type]);
    }

    public function update(EmployeeTypeRequest $request){
        $res=EmployeeType::where('id',$request->id)->update($request->validated());
        return response()->json(['results'=>$res]);
    }

    public function destroy(Request $request){
       EmployeeType::where('id',$request->route('id'))->delete();
       return true;
    }

}
