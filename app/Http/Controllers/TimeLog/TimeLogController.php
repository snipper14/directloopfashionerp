<?php

namespace App\Http\Controllers\TimeLog;

use App\TimeLog\TimeLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\TimeLog\TimeLogRequest;

class TimeLogController extends BaseController
{
    public function create(TimeLogRequest $request){
        $data=TimeLog::create($request->validated()+Parent::user_branch_id());
        return response()->json(['results'=>$data]);
    }

    public function update(TimeLogRequest $request){
       $data=TimeLog::where('id',$request->id)->update($request->validated());
       return response()->json(['results'=>$data]);
    }
    public function fetch(){
        $raw_query = TimeLog::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('logged_on', 'LIKE', '%' . request('search') . '%')
                ->orWhere('no_hours', 'LIKE', '%' . request('search') . '%')
                ->orWhere('time_in', 'LIKE', '%' . request('search') . '%')
                ->orWhere('time_out', 'LIKE', '%' . request('search') . '%')
                ->orWhere('total_amount', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('employee', function($query) {
                    $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                   ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                });
              
            });
      })->with(['employee'])->orderBy('id', 'DESC');
     $data=  $raw_query->get();
      if(request('page')>0){
          $data=$raw_query->paginate(15);
      }
      return response()->json(['results' => $data]);
    }
  

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $id = TimeLog::where('id',$id);
        $id->delete();
        return response()->json(['results' => 'deleted']);
    }
}
