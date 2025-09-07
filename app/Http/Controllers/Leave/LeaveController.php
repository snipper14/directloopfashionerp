<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Leave\Leave;
use Illuminate\Http\Request;

class LeaveController extends BaseController
{
    function create(Request $request)
    {
       
       $data= $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'start_from' => 'required',
            'end_at' => "required"

        ]);
        Leave::create($data + ['user_id' => Parent::user_id()]);
        return true;
    }
    function fetch(){
        $main_query=Leave::with(['employee','leave_type'])
        ->when(request('search', '') != '', function ($query) {
            $query->whereHas('employee', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    } )
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    } )
                    ->orWhereHas('leave_type', function ($query) {
                        $query->where('leave_type', 'LIKE', '%' . request('search') . '%');
                       
                    } );
            });
      
        
       $res=$main_query-> get();
       if(request('page')>0){
        $res=$main_query->paginate(20);
       }
       return response()->json($res);
    }
    function destroy(Request $request){
        $res=Leave::where('id',$request->route('id'))->delete();
        return $res;
    }
}
