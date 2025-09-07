<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\ProductMappingRequest;
use App\stock\ProductMapping;
use Illuminate\Http\Request;

class ProductMappingController extends BaseController
{
    public function create(ProductMappingRequest $request)
    {

        ProductMapping::create($request->validated() + Parent::user_branch_id());
        $res = ProductMapping::with(["element", 'stock'])->where(['stock_id' => $request->stock_id])->get();
        return response()->json($res);
    }
    function fetch()
    {
        $res = ProductMapping::with(["element", 'stock'])->where(['stock_id' => request('stock_id')])->get();
        return response()->json($res);
    }
    function destroy(Request $request)
    {
        ProductMapping::where(['id' => $request->route('id')])->delete();
        return true;
    }
    function fetchQty()
    {
   
        $res = ProductMapping::with(["element", 'stock'])->where([
            'stock_id' => request('stock_id'),
            'element_id' => request('element_id')
        ])->first();
        return response()->json($res);
    }
}
