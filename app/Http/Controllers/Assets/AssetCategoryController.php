<?php

namespace App\Http\Controllers\Assets;

use App\Assets\AssetCategory;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetCategoryController extends BaseController
{
    function create(Request $request){
        $data =  $request->validate([

            'category' => 'required|unique:asset_categories,category,NULL,id,deleted_at,NULL',
            'description' => 'nullable',
            

        ]);
        AssetCategory::create($data);
        $res = AssetCategory::get();
        return response()->json($res);
    }
    public function update(Request $request)
    {
        $data =  $request->validate([

            'category' => 'required|unique:asset_categories,category,' . $request->id . ',id,deleted_at,NULL',
            'description' => 'nullable',
           
            'id' => 'required'
        ]);
        AssetCategory::where('id', $request->id)->update($data);
        
        $res = AssetCategory::get();
        return response()->json($res);
        
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        AssetCategory::find($id)->delete();
        return true;
    }
    public function fetch()
    {
        $res = AssetCategory::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('category', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
}
