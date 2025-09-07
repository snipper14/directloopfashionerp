<?php

namespace App\Http\Controllers\Allowance;

use App\Allowance\Allowance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Allowance\AllowanceRequest;

class AllowanceController extends BaseController
{
    public function create(AllowanceRequest $request){
        $data=Allowance::create($request->validated()+Parent::user_branch_id());
        return true;
     }
     public function update(AllowanceRequest $request){
         Allowance::where('id',$request->id)->update($request->validated());
         return true;
     }
     public function fetch(){
         $raw_query = Allowance::when(request('search', '') != '', function ($query) {
             $query->where(function ($q) {
                 $q->where('amount', 'LIKE', '%' . request('search') . '%')
                 ->orWhere('allowance_type', 'LIKE', '%' . request('search') . '%')
                  ->orWhereHas('employee', function($query) {
                     $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                 });
               
               
             });
         }) ->with(['employee',])->orderBy('id', 'DESC');
     $data=  $raw_query->get();
     if(request('page')>0){
         $data=$raw_query->paginate(20);
     }
         return response()->json(['results'=>$data]);
     }
   
     
     public function destroy(Request $request)
     {
         $id = $request->route('id');
         $id = Allowance::find($id);
         $id->delete();
         return response()->json(['results' => 'deleted']);
     }
}
