<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class DrinksItemController extends BaseController
{
    function create(Request $request)
    {
        $stock_array = $request->stock_id;
        foreach ($stock_array as $stock_id) {
            Stock::where('id', $stock_id)->update(['isDrinkProduct' => 1]);
        }
        $res = Stock::where(['isDrinkProduct' => 1])->get();
        return response()->json($res);
    }

    function remove(Request $request){
        Stock::where(['id'=>$request->route('id')])->update(['isDrinkProduct' => 0]);
        $res = Stock::where(['isDrinkProduct' => 1])->get();
        return response()->json($res);
    }
    function fetch(){
        $res = Stock::where(['isDrinkProduct' => 1])->get();
        return response()->json($res);
    
    }
}
