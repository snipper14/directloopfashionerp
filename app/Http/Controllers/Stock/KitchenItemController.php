<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Stock\Stock;
use Illuminate\Http\Request;

class KitchenItemController extends BaseController
{
    function create(Request $request)
    {
        $stock_array = $request->stock_id;
        foreach ($stock_array as $stock_id) {
            Stock::where('id', $stock_id)->update(['isKitchenProduct' => 1]);
        }
        $res = Stock::where(['isKitchenProduct' => 1])->get();
        return response()->json($res);
    }

    function remove(Request $request){
        Stock::where(['id'=>$request->route('id')])->update(['isKitchenProduct' => 0]);
        $res = Stock::where(['isKitchenProduct' => 1])->get();
        return response()->json($res);
    }
    function fetch(){
        $res = Stock::where(['isKitchenProduct' => 1])->get();
        return response()->json($res);
    
    }
}
