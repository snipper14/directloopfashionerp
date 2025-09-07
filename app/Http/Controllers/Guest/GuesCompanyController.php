<?php

namespace App\Http\Controllers\Guest;

use App\Guest\GuestCompany;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\GuesCompanyRequest;
use Illuminate\Http\Request;

class GuesCompanyController extends BaseController
{
    function create(GuesCompanyRequest $request)
    {

        GuestCompany::create($request->validated() + Parent::user_branch_id());
        return true;
    }

    function update(GuesCompanyRequest $request)
    {
        GuestCompany::where('id', $request->id)->update($request->validated());
        return true;
    }
    public function fetch()
    {
        $main_query = GuestCompany::orderBy('id', 'DESC');
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('address', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contacts', 'LIKE', '%' . request('search') . '%');
            });
        });

        $res = $main_query->get();
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        GuestCompany::where('id', $id)->delete();
        return true;
    }
    public function getCompanyName(Request $request){
        $id = $request->route('id');
       $res= GuestCompany::where('id', $id)->first();
        return response()->json($res);
    }
}
