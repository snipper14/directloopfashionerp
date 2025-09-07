<?php

namespace App\Http\Controllers\Waste;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\WasteReasonRequest;
use App\Stock\WasteReason;
use Illuminate\Http\Request;

class WasteReasonController extends BaseController
{
    public function create(WasteReasonRequest $request){
        WasteReason::create($request->validated()+Parent::user_branch_id());
        $res=WasteReason::get();
        return response()->json($res);
        return true;
    }
    function fetch(){
        $res = WasteReason::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('reasons', 'LIKE', '%' . request('search') . '%');
            });
        })->get();
        return response()->json($res);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');

        WasteReason::find($id)->delete();
        return response()->json(['message' => "record deleted"]);
    }

    public function update(WasteReasonRequest $request)
    {
        WasteReason::where('id', $request->id)->update($request->validated());
        $res = WasteReason::orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
}
