<?php

namespace App\Http\Controllers\StockReturn;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\StockReturns\StockReturn;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\StockReturns\StockReturnRequest;
use App\Stock\Stock;
use phpDocumentor\Reflection\Types\Parent_;

class StockReturnController extends BaseController
{
    public function create(StockReturnRequest $request)
    {

        DB::transaction(function () use ($request) {
            if ($this->containsItem($request)) {

                StockReturn::where([
                    'return_code' => $request->return_code,
                    'user_id' => Parent::user_id(),
                    'stock_id' => $request->stock_id
                ])
                    ->update(array('qty_returned' => DB::raw('qty_returned + ' . $request->qty_returned)));
            } else {
                StockReturn::create($request->validated() + Parent::user_branch_id());
            }
            Stock::where(["id" => $request->stock_id])->update(array('qty' => DB::raw('qty + ' . $request->qty_returned)));
        }, 5);
        $res = StockReturn::with(['stock'])->where([
            'return_code' => $request->return_code,
            'status' => 'progress'
        ] + Parent::user_branch_id())->get();
        return response()->json($res);
    }


    function containsItem($request)
    {
        $res = StockReturn::where([
            'stock_id' => $request->stock_id,
            'return_code' => $request->return_code,
            'status' => "progress",

        ])->get();
        return  $res->isEmpty() ? false : true;
    }

    function fetchCode()
    {
        return response()->json($this->return_code());
    }
    function return_code()
    {
        $latest = StockReturn::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $return_code = $today . '-' . 'RT' . sprintf('%04d', $string);
        $isUnique = StockReturn::where('return_code', $return_code)->first();
        if ($isUnique) {
            $return_code = $today . '-' . 'RT' . ($latest->id + 1);
        }
        return  $return_code;
    }

    function destroy(Request $request)
    {
        StockReturn::where(["id" => $request->id])->delete();
        Stock::where(["id" => $request->stock_id])->update(array('qty' => DB::raw('qty - ' . $request->qty_returned)));
        return true;
    }
    function fetch()
    {
        $raw_query = StockReturn::with(['stock', 'return_user', 'user'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('return_code', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('return_user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('stock', function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })->where(Parent::branch_array())->orderBy('id', 'DESC');

        if (request('page') > 0) {
            $res = $raw_query->paginate(30);
        } else {
            $res = $raw_query->get();
        }
        return response()->json($res);
    }
}
