<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\AccompanimentRequest;
use App\Rules\CheckIfAccompanimentIsUnique;
use App\Stock\Accompaniment;
use Illuminate\Http\Request;

class AccompanimentController extends BaseController
{

    public function create(AccompanimentRequest $request)
    {
        $this->validate($request, ['accompaniment_array_id' => new CheckIfAccompanimentIsUnique($request->stock_id)]);
        $accompaniment_array = $request->accompaniment_array_id;
        $stock_id = $request->stock_id;
        $category_id = $request->category_id;
        foreach ($accompaniment_array as $accompaniment_id) {
            Accompaniment::create([
                'accompaniment_id' => $accompaniment_id,
                'stock_id' => $stock_id,
                'category_id' => $category_id
            ] + Parent::user_branch_id());
        }

        $res = Accompaniment::with([
            'accompaniment', 'stock', 'category'
        ])->where(["stock_id" => $request->stock_id])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
    function fetch()
    {

        $res = Accompaniment::with([
            'accompaniment', 'stock', 'category'
        ])->where(["stock_id" => request('stock_id')])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
    function destroy(Request $request)
    {

        $id = $request->route('id');
        Accompaniment::where('id', $id)->delete();
        return true;
    }
}
