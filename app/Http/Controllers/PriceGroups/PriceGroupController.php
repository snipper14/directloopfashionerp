<?php

namespace App\Http\Controllers\PriceGroups;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Stock\PriceGroup;
use App\Stock\PriceGroupAmount;
use Illuminate\Http\Request;

class PriceGroupController extends BaseController
{
    function create(Request $request)
    {
        $this->validate($request, [

            'price_group' => 'required|unique:price_groups,price_group,NULL,id,deleted_at,NULL',
        ]);
        PriceGroup::create(['price_group' => $request->price_group]);
        return true;
    }
    function update(Request $request)
    {
        $this->validate($request, [

            'price_group' => 'required|unique:price_groups,price_group,'.$request->id.',id,deleted_at,NULL',
        ]);
        PriceGroup::where(['id'=>$request->id])->update(['price_group' => $request->price_group]);
        return true;
    }
    function fetch()
    {
        $query = PriceGroup::when(request('search') != '', function ($q) {
            $q->where('price_group', 'LIKE', '%' . request('search') . '%');
        })->latest();
        $data = $query->get();
        if (request('page') > 0) {
            $data = $query->paginate(40);
        }
        return response()->json($data);
    }

    function destroy(Request $request)
    {
        $id = $request->route('id');
        PriceGroup::where(['id' => $id])->delete();
        PriceGroupAmount::where(['price_group_id'=>$id])->delete();
        return true;
    }
}
