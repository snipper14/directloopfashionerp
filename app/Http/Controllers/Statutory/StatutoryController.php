<?php

namespace App\Http\Controllers\Statutory;

use App\Statutory\Statutory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Statutory\StatutoryRequest;

class StatutoryController extends BaseController
{
    public function create(StatutoryRequest $request){
        Statutory::create($request->validated()+Parent::user_branch_id());
        return true;
}
public function update(StatutoryRequest $request){
   Statutory::where('id',$request->id)->update($request->validated());
   return true;
}

public function fetch(){
    $raw_query = Statutory::when(request('search', '') != '', function ($query) {
        $query->where(function ($q) {
            $q->where('statutory_type', 'LIKE', '%' . request('search') . '%')
            ->orWhere('salary_from', 'LIKE', '%' . request('search') . '%')
            ->orWhere('salary_to', 'LIKE', '%' . request('search') . '%')
            ->orWhere('amount_deducted', 'LIKE', '%' . request('search') . '%');
            });
    })->orderBy('id', 'DESC');
    $data=$raw_query->get();
    if(request('page')>0){
        $data=$raw_query->paginate(20);  
    }
    return response()->json(['results'=>$data]);
}



public function destroy(Request $request)
{
    $id = $request->route('id');
    $id = Statutory::find($id);
    $id->delete();
    return response()->json(['results' => 'deleted']);
}
}
