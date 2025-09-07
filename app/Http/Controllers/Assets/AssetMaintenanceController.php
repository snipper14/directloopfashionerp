<?php

namespace App\Http\Controllers\Assets;

use App\Assets\AssetMaintenance;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssetMaintenanceRequest;
use Illuminate\Http\Request;

class AssetMaintenanceController extends BaseController
{
    public function create(AssetMaintenanceRequest $request){
        AssetMaintenance::create($request->validated()+Parent::user_branch_id());
        return true;
    }

    public function fetch(){
        $main_query = AssetMaintenance::with(['asset', 'user'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('priority', 'LIKE', '%' . request('search') . '%')
                ->orwhere('type', 'LIKE', '%' . request('search') . '%')
                    
                    ->orWhereHas('asset', function ($q) {
                        $q->where('asset_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->orderBy('id', 'DESC');
        
        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(16);
        }
        return response()->json($data);   
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        AssetMaintenance::find($id)->delete();
        return true;
    }
    function updateStatus(Request $request){
         $this->validate($request, [

            'type' => 'required',
         

        ]);
        AssetMaintenance::where('id',$request->id)->update(['type'=>$request->type]);
        return true;
    }
}
