<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\SplitProductSettingsRequest;
use App\Rules\ValidateUniqueSplitProduct;
use App\Stock\SplitProductSetting;
use Illuminate\Http\Request;

class SplitProductSettingController extends BaseController
{
    public function create(SplitProductSettingsRequest $request)
    {
        $this->validate($request, ['final_product_id' => new ValidateUniqueSplitProduct($request->original_product_id)]);
        SplitProductSetting::create($request->validated() + Parent::user_branch_id());
        $res = SplitProductSetting::with(['original_product', 'final_product'])->where('original_product_id', $request->original_product_id)->get();

        return response()->json($res);
    }

    public function fetch(Request $request)
    {
        $res = SplitProductSetting::with(['original_product', 'final_product'])->where('original_product_id', $request->route('id'))->get();
        return response()->json($res);
    }

    function destroy(Request $request)
    {
        SplitProductSetting::where(['original_product_id' => $request->original_product_id, 'final_product_id' => $request->final_product_id])->delete();
        return true;
    }
}
