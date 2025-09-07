<?php

namespace App\Http\Controllers\Assets;

use App\Assets\AssetAllocation;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssetAllocationRequest;
use Illuminate\Http\Request;

class AssetAllocationController extends BaseController
{
    function create(AssetAllocationRequest $request)
    {
        AssetAllocation::create($request->validated() + Parent::user_branch_id());
        return true;
    }

    public function fetch()
    {
        $main_query = AssetAllocation::with(['asset', 'employee'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('allocation_code', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($q) {
                        $q->whereRaw("concat(first_name, ' ', last_name) like '%" . request('search') . "%'")

                            ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('phone', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('asset', function ($q) {
                        $q->where('asset_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->orderBy('id', 'DESC');
        if (request('status') == 'revoked') {
            $main_query->where('allocation_status', 'revoked');
        }else{
            $main_query->where('allocation_status', 'allocated');
        }
        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(16);
        }
        return response()->json($data);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        AssetAllocation::find($id)->delete();
        return true;
    }
    public function revoke(Request $request)
    {
        $id = $request->route('id');
        AssetAllocation::find($id)->update(['allocation_status'=>'revoked']);
        return true;
    }
    function reAllocate(Request $request){
        $id = $request->route('id');
        AssetAllocation::find($id)->update(['allocation_status'=>'allocated']);
        return true;
    }
}
