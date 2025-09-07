<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollProductionRequest;
use App\Payroll\PayrollProduction;
use Illuminate\Http\Request;

class ProductionController extends BaseController
{
    public function create(PayrollProductionRequest $request)
    {
          $data = PayrollProduction::create($request->validated()+Parent::user_branch_id());
          return response()->json(['results' => $data]);
    }

    public function update(PayrollProductionRequest $request)
    {
          $data = PayrollProduction::where('id', $request->id)->update($request->validated());
          return response()->json(['results' => $data]);
    }
    public function fetch()
    { 
          $raw_query = PayrollProduction::when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('produced_on', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('qty', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('size', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('colour', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                       ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                       
                      
                    });
                });
          })->with(['employee','stock'])->orderBy('id', 'DESC')->where(Parent::branch_array());
           $data=  $raw_query->get();
           if(request('page')>0){
               $data=$raw_query->paginate(15);
           }
          return response()->json(['results' => $data]);
    }
   

    public function destroy(Request $request)
    {
          $id = $request->route('id');
          $id = PayrollProduction::where('id',$id);
          $id->delete();
          return response()->json(['results' => 'deleted']);
    }
}
