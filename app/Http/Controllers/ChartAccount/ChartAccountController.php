<?php

namespace App\Http\Controllers\ChartAccount;

use App\Accounts\ChartAccount;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChartAccountController extends BaseController
{
    function create(Request $request)
    {
        $data =  $request->validate([

            'name' => 'required|unique:chart_accounts,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable',
            'type' => 'required'

        ]);
        ChartAccount::create($data);
        $res = ChartAccount::get();
        return response()->json($res);
    }
    public function update(Request $request)
    {
        $data =  $request->validate([

            'name' => 'required|unique:chart_accounts,name,' . $request->id . ',id,deleted_at,NULL',
            'description' => 'nullable',
            'type' => 'required',
            'id' => 'required'
        ]);
        ChartAccount::where('id', $request->id)->update($data);
        return true;
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        ChartAccount::find($id)->delete();
        return true;
    }
    public function fetch()
    {
        $res = ChartAccount::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
    
}
