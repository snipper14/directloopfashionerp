<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\ProductionCost\ProductionCost;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\ProductionCostRequest;

class ProductionCostController extends BaseController
{


    function create(ProductionCostRequest $request)
    {

        ProductionCost::updateOrCreate(
            ['stock_id' => $request->stock_id,'product_id' => $request->product_id,'details'=>$request->details],
            $request->validated() + ['user_id' => Parent::user_id()]
        );

        ProductionCost::where(['stock_id' => $request->stock_id])->update(
            ['qty_produced'=>$request->qty_produced]   );

        $res = ProductionCost::where('stock_id', $request->stock_id)->get();
        return response()->json($res);
    }

  
    function fetch(Request $request)
    {
        $id = $request->route('id');
        $res = ProductionCost::with([ 'stock'])->where(['stock_id' => $id])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        ProductionCost::find($id)->delete();
        return true;
    }
    function updateProductionCost(Request $request)
    {
        $this->validate($request, [
            'id' => "required",
            'total_cost' => 'numeric|required',

        ]);
        Stock::where(['id' => $request->id])->update(['production_cost' => $request->total_cost]);

        return true;
    }
}
