<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Location\TableRequest;
use App\Location\Table;
use Illuminate\Http\Request;

class TableController extends BaseController
{
    public function create(TableRequest $request){
        Table::create($request->validated()+Parent::user_branch_id());
        $res=Table::with(['department'])->where(Parent::branch_array())->orderBy('id','DESC')->get();
        return response()->json($res);
    }
    public function fetch(){
    
        $raw_query = Table::with(['department','orders'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('seat_type', 'LIKE', '%' . request('search') . '%')
                ->orWhere('details', 'LIKE', '%' . request('search') . '%')
                ->orWhere('department_id',  request('search') )
               ->
                orWhereHas('department', function($query) {
                    $query->where('department', 'LIKE', '%' . request('search') . '%');
                  
                });
              
              
            });
        }) ;
        if(request('department_id')){
            $raw_query->where("department_id",request('department_id'));
        }
        $raw_query->with(['department'])->orderBy('id', 'DESC')->where(Parent::branch_array());
        $data=$raw_query->get();
        if(request('page')>0){
            $data=$raw_query->paginate(10);
        }
        return response()->json($data);
    }

    public function update(TableRequest $request){
        Table::where('id',$request->id)->update($request->validated());
        $res=Table::with(['department'])->where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request){
        Table::where('id',$request->route('id'))->delete();
        return true;
    }
}
