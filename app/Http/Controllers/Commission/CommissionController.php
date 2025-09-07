<?php

namespace App\Http\Controllers\Commission;

use Illuminate\Http\Request;
use App\Commission\Commission;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Commission\CommissionRequest;

class CommissionController extends BaseController
{
    public function create(CommissionRequest $request)
    {
        Commission::create($request->validated()+Parent::user_branch_id());
        return true;
    }
    public function update(CommissionRequest $request)
    {
        Commission::where('id', $request->id)->update($request->validated());
        return true;
    }

    public function fetch()
    {
        $raw_query = Commission::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('amount', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_month', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['employee',])->orderBy('id', 'DESC');
           $data=$raw_query->get();
           if(request('page')>0){
            $data=$raw_query->paginate(20);
           }
        return response()->json(['results' => $data]);
    }

  

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $id = Commission::find($id);
        $id->delete();
        return response()->json(['results' => 'deleted']);
    }
}
