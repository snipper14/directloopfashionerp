<?php

namespace App\Http\Controllers\DispatchReq;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\DispatchReq\DispatchReqRequest;
use App\ReqDispatch\ReqDispatch;
use App\Requistion\Requistion;
use App\Rules\CheckStockAvailablity;
use App\Stock\Stock;

class DispatchReqController extends BaseController
{
    function create(DispatchReqRequest $request)
    {
        $this->validate($request, [
            'stock_id' => new CheckStockAvailablity($request->dispatch_qty),

        ]);
        DB::transaction(function () use ($request) {
            ReqDispatch::create($request->validated() +
                [
                    'branch_id' => Parent::branch_id(),
                    'issuer_id' => Parent::user_id()
                ]);
            Requistion::where([
                'req_id' => $request->req_id,
                'stock_id' => $request->stock_id,
            ])->increment('qty_issued', $request->dispatch_qty);
            if ($request->decrementStock) {
                Stock::where([
                    'id' => $request->stock_id,
                ])->update(['qty' => DB::raw('qty - ' . $request->dispatch_qty)]);
            }
        }, 5);
        $user_id = $request->user_id;
        $res =   Requistion::with(['user', 'department', 'stock',])->where(
            [
                'approved' => '1',
                'user_id' => $user_id,
                'branch_id' => Parent::branch_id()
            ]
        )->whereRaw('qty > qty_issued')->get();
        return response()->json($res);
    }


    function fetchDispatchReport()
    {
        $user_id = request('user_id');
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',
            'user_id'
        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query =   ReqDispatch::with(['user',  'stock', 'issuer'])->where(
            [


                'branch_id' => Parent::branch_id()
            ]
        );
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('issue_date', [$from, $to]);
        });
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {


                if ($column == 'req_id') {
                    $query->where($column, $value);
                } else if ($column == 'product_name') {
                    $query->whereHas('stock', function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', '%' . $value . '%');
                    });
                } else if ($column == 'user_id') {

                    $query->where($column, $value);
                }
            }
        });
        $raw_query->orderBy('id', 'DESC');
        if (request('page') > 0) {
            $res = $raw_query->paginate(10);
        } else {
            $res = $raw_query->get();
        }
        return response()->json($res);
    }
}
