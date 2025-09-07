<?php

namespace App\Http\Controllers\Ingredient;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Ingredient\Ingredient;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Ingredients\IngredientsRequest;

class IngredientController extends BaseController
{
    function create(IngredientsRequest $request)
    {
          DB::transaction(function () use ($request) {
        Ingredient::updateOrCreate(
            ['ingredient_id' => $request->ingredient_id, 'stock_id' => $request->stock_id],
            $request->validated() + ['user_id' => Parent::user_id()]
        );
        Ingredient::where(['stock_id' => $request->stock_id])->update(

            ['produced_qty' => $request->produced_qty]
        );
         },5);
        
        $res = Ingredient::where(['stock_id' => $request->stock_id])->with(['ingredient.unit', 'stock'])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
   
    function fetch(Request $request)
    {
        $id = $request->route('id');
        $res = Ingredient::with(['ingredient.unit', 'stock.unit'])->where(['stock_id' => $id])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        Ingredient::find($id)->delete();
        return true;
    }
}
