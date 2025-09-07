<?php

namespace App\Http\Controllers\Dept;

use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\dept\DepartmentRequest;

class DepartmentController extends BaseController
{
    function create(DepartmentRequest $request)
    {
        Department::create($request->validated() + ['user_id' => Parent::user_id()]);
        $res = Department::withoutGlobalScope(BranchScope::class)->with('branch')->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    public function update(DepartmentRequest $request)
    {
        Department::withoutGlobalScope(BranchScope::class)->where('id', $request->id)->update($request->validated());

        $res = Department::withoutGlobalScope(BranchScope::class)->with('branch')->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');

        Department::find($id)->delete();
        return response()->json(['message' => "record deleted"]);
    }

    public function fetch()
    {
        $res = Department::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('department', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
    public function fetchAll()
    {
        $res = Department::withoutGlobalScope(BranchScope::class)->with(['branch'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('department', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
    public function fetchAllDept()
    {
        $res = Department::withoutGlobalScope(BranchScope::class)->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('department', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
    public function fetchPOSDept()
    {
        $res = Department::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('department', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%');
            });
        })->where('show_pos', "1")->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
}
