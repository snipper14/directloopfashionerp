<?php

namespace App\Http\Controllers\Stock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\SplitProductRequest;
use App\Rules\ValidateSplitQty;
use App\Stock\SplitProduct;
use App\Stock\Stock;

class SplitProductController extends BaseController
{
    public function create(SplitProductRequest  $request)
    {
        $this->validate($request, ['qty_split' => new ValidateSplitQty($request->original_product_id)]);
        DB::transaction(function () use ($request) {
            SplitProduct::create($request->validated() + Parent::user_branch_id());
            Stock::where('id', $request->original_product_id)->decrement('qty', $request->qty_split);
            Stock::where('id', $request->final_product_id)->increment('qty', $request->final_qty);
        }, 5);
    }

    function fetch()
    {
        $from = request('from');
        $to = request('to');
        $main_query = SplitProduct::with(['user', 'original_product', 'final_product', 'split_product_setting']);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search') != '', function ($query) {
            $query->whereHas('original_product', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', 'LIKE', '%' . request('search') . '%');
            })
                ->orWhereHas('final_product', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', 'LIKE', '%' . request('search') . '%');
                });
        });
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->orderBy('id', 'DESC')->paginate(20);
        }
        return response()->json($res);
    }
}
