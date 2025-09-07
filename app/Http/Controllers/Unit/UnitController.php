<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\UnitRequest;
use App\Unit\Unit;
use Illuminate\Http\Request;

class UnitController extends BaseController
{
    function create(UnitRequest $request)
    {
        Unit::create($request->validated() +['user_id'=> Parent::user_id()] + ['isSync' => 0]);
        $res = Unit::orderBy('id', 'DESC')->paginate(50);
        return response()->json($res);
    }

    public function update(UnitRequest $request)
    {
        Unit::where('id', $request->id)->update($request->validated());
        $res = Unit::orderBy('id', 'DESC')->paginate(50);
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');

        Unit::find($id)->delete();
        return response()->json(['message' => "record deleted"]);
    }

    public function fetch()
    {
        $res = Unit::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->paginate(50);

        return response()->json($res);
    }
    public function fetchAll()
    {
        $res = Unit::orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
}
