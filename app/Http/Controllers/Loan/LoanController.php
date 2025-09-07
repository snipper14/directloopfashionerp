<?php

namespace App\Http\Controllers\Loan;

use App\Loan\Loan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Loan\CoLoanRequest;
use App\Http\Requests\Loan\LoansRequest;

class LoanController extends BaseController
{
    public function create(LoansRequest $request){
        $data=Loan::create($request->validated()+Parent::user_branch_id());
        return response()->json(['results'=>$data]);
      }
    public function createCoLoans(CoLoanRequest $request){
      $data=Loan::create($request->validated()+["outstanding_balance"=>$request->loan_amount]+Parent::user_branch_id());
        return response()->json(['results'=>$data]);
    }
      public function update(LoansRequest $request){
           $data=Loan::where('id',$request->id)->update($request->validated());
           return true;
      }
      public function fetch(){
          $raw_query = Loan::when(request('search', '') != '', function ($query) {
              $query->where(function ($q) {
                  $q->Where('description', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('loan_date', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('loan_amount', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('installment_amount', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('outstanding_balance', 'LIKE', '%' . request('search') . '%')
                  ->orWhere('completion_date', 'LIKE', '%' . request('search') . '%')
                  ->orWhereHas('employee', function($query) {
                      $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                     ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                  });
                
                
              });
          }) ->with(['employee',])->orderBy('id', 'DESC');
          $data=$raw_query->get();
          if(request('page')>0){
              $data=$raw_query->paginate(10);
          }
          return response()->json(['results'=>$data]);
      }
  
      function fetchByUser(){
          $data = Loan::where(["employee_id"=>request('id')])->with(['employee',])->orderBy('id', 'DESC')->get();
        
          return response()->json($data);
      }
  
     
  
      public function destroy(Request $request)
      {
          $id = $request->route('id');
          $id = Loan::find($id);
          $id->delete();
          return response()->json(['results' => 'deleted']);
      }
}
