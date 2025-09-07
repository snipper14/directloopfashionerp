<?php

namespace App\Http\Controllers\Casuals;

use Illuminate\Http\Request;
use App\Casuals\CasualTimelog;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Casual\CasualTimelogRequest;

class CasualsController extends BaseController
{
    public function create(CasualTimelogRequest $request){
        CasualTimelog::create($request->validated()+Parent::user_branch_id());
        return true;
    }

    public function fetch(){
        $raw_query = CasualTimelog::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('date_recorded', 'LIKE', '%' . request('search') . '%')
                ->orWhere('no_hrs', 'LIKE', '%' . request('search') . '%')
                ->orWhere('time_in', 'LIKE', '%' . request('search') . '%')
                ->orWhere('time_out', 'LIKE', '%' . request('search') . '%')
                ->orWhere('amount', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('employee', function($query) {
                    $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                   ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                });
              
            });
      })->with(['employee'])->orderBy('id', 'DESC');
      $data=$raw_query->get();
      if(request('page')>0){

        $data=$raw_query->paginate(10);
      }
      return response()->json(['results' => $data]);
    }
  

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $id = CasualTimelog::where('id',$id);
        $id->delete();
        return response()->json(['results' => 'deleted']);
    }
}
