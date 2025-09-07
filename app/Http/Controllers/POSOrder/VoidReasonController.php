<?php

namespace App\Http\Controllers\POSOrder;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Sale\VoidReason;
use Illuminate\Http\Request;

class VoidReasonController extends BaseController
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'details' => 'required|max:255',

        ]);
        VoidReason::create($validated + Parent::user_branch_id());
        $res = VoidReason::get();
        return response()->json($res);
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'details' => 'required|max:255',
            "id" => "required"
        ]);

        VoidReason::where("id", $request->id)->update($validated + Parent::user_branch_id());
        $res = VoidReason::get();
        return response()->json($res);
    }
    public function fetch()
    {
        $res = VoidReason::orderBy("id", "DESC")->get();
        return response()->json($res);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        VoidReason::where("id", $id)->delete();
        return true;
    }
}
