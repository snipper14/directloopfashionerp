<?php

namespace App\Http\Controllers\Guest;

use App\Customer\Customer;
use App\Guest\Guest;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\GuestRequest;
use Illuminate\Http\Request;

class GuestController extends BaseController
{
    public function create(GuestRequest $request)
    {
        $insert_data = array(
            'company_name' => $request->name,
            'company_phone' => $request->phone,
            'postal_address' => $request->postal_address,
            'country' => $request->country_origin,
            'acc_code'=>Parent::customerCode()

        );
        $cust_res = Customer::create($insert_data + Parent::branch_array());
        $res = Guest::create($request->validated() + Parent::user_branch_id() + ['customer_id' => $cust_res->id]);
        return response()->json($res);
    }

    function fetch()
    {
        Customer::where(Parent::branch_array());
        $raw_query = Guest::with(['guest_company'])->
        when(request('guest_id','') !='',function($query){
             $query->where('id',request('guest_id'));
        })->
        when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%');
            });
        })->where(Parent::branch_array())->orderBy('id', 'DESC');
        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(20);
        }
        return response()->json($data);
    }
    function update(GuestRequest $request)
    {
        $res = Guest::where('id', $request->id)->update($request->validated());
        print_r($res);
        $insert_data = array(
            'company_name' => $request->name,
            'company_phone' => $request->phone,
            'postal_address' => $request->postal_address,
            'country' => $request->country_origin,

        );
        $cust_res = Customer::where('id', $request->customer_id)->update($insert_data + Parent::branch_array());
        return response()->json($cust_res);
    }

    function fetchGuestDetails(){
        $res=Guest::with(['guest_company'])->where('id',request('guest_id'))->first();
        return response()->json($res);
    }

    function destroy(Request $request){
        $id=$request->route('id');
        Guest::where('id',$id)->delete();
        return true;
    }
}
