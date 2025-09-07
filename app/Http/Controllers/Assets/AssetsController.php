<?php

namespace App\Http\Controllers\Assets;

use App\Assets\Asset;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\AssetRequest;
use Illuminate\Http\Request;

class AssetsController extends BaseController
{
    public function create(AssetRequest $request)
    {
        Asset::create($request->validated() + Parent::user_branch_id());
        $res = Asset::get();
        return response()->json($res);
    }
    public function update(AssetRequest $request)
    {
       
        Asset::where('id', $request->id)->update($request->validated());
        
        $res = Asset::get();
        return response()->json($res);
        
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        Asset::find($id)->delete();
        return true;
    }
    public function fetch()
    {
        $main_query = Asset::with(['asset_category'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('asset_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('asset_code', 'LIKE', '%' . request('search') . '%')
                ->orWhere('model_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('serial_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('purchase_type', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('asset_category',function($q){
                    $q->where('category', 'LIKE', '%' . request('search') . '%');
                });
            });
        })->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }
    public function fetchTotal()
    {
        $main_query = Asset::with(['asset_category'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('asset_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('asset_code', 'LIKE', '%' . request('search') . '%')
                ->orWhere('model_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('serial_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('purchase_type', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('asset_category',function($q){
                    $q->where('category', 'LIKE', '%' . request('search') . '%');
                });
            });
        })->selectRaw('SUM(purchase_price) AS total_purchase');
        $res = $main_query->first();
     
        return response()->json($res);
    }
}
