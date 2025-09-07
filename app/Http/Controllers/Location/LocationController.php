<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Location\LocationRequest;
use App\Location\Location;
use Illuminate\Http\Request;

class LocationController extends BaseController
{
    public function create(LocationRequest $request){
        Location::create($request->validated()+Parent::user_branch_id());
        $res=Location::where(Parent::branch_array())->get();
        return response()->json($res);
    }
    public function fetch(){
        $raw_query = Location::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->Where('name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('details', 'LIKE', '%' . request('search') . '%');
              
            });
        }) ->orderBy('id', 'DESC')->where(Parent::branch_array());
        $data=$raw_query->get();
        if(request('page')>0){
            $data=$raw_query->paginate(10);
        }
        return response()->json($data);
    }
   

    public function update(LocationRequest $request){
        Location::where('id',$request->id)->update($request->validated());
        $res=Location::where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request){
        Location::where('id',$request->route('id'))->delete();
        return true;
    }
}
