<?php

namespace App\Http\Controllers\Deduction;

use App\Deduction\Deduction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Deduction\DeductionRequest;

class DeductionConroller extends BaseController
{
    public function create(DeductionRequest $request){
        $data=Deduction::create($request->validated()+["outstanding_balance"=>$request->deduction_total]+Parent::user_branch_id());
        return true;
  
      }
      public function update(DeductionRequest $request){
          Deduction::where('id',$request->id)->update($request->validated()+["outstanding_balance"=>$request->deduction_total]);
          return true;
      }
  
      public function fetch(){
          $raw_query = Deduction::when(request('search', '') != '', function ($query) {
              $query->where(function ($q) {
                  $q->where('deduction_type', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('monthy_deduction', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('deduction_total', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('effective_date', 'LIKE', '%' . request('search') . '%')
                   ->orWhereHas('employee', function($query) {
                      $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                     ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                  });
                
                
              });
          }) ->with(['employee',])->orderBy('id', 'DESC');
       $data= $raw_query->get();
       if(request('page')>0){
        $data= $raw_query->paginate(10); 
       }
          return response()->json(['results'=>$data]);
      }
  
      
      public function destroy(Request $request)
      {
          $id = $request->route('id');
          $id = Deduction::find($id);
          $id->delete();
          return response()->json(['results' => 'deleted']);
      }
}
