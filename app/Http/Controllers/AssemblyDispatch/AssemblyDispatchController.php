<?php

namespace App\Http\Controllers\AssemblyDispatch;

use App\Stock\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\CheckStockAvailablity;
use App\Http\Controllers\BaseController;
use App\Assembly\AssemblyProductRequisition;
use App\DispatchAssemblyReq\DispatchAssemblyReq;
use App\Http\Requests\AssemblyDispatch\AssemblyDispatchRequest;

class AssemblyDispatchController extends BaseController
{
    public function create(AssemblyDispatchRequest $request)
    {

        $this->validate($request, [
            'ingredient_id' => new CheckStockAvailablity($request->dispatch_qty),

        ]);
        DB::transaction(function () use ($request) {
            DispatchAssemblyReq::create($request->validated() +  [
                'branch_id' => Parent::branch_id(),
                'issuer_id' => Parent::user_id()
            ]);
            AssemblyProductRequisition::where([
                'id' => $request->id,
                'req_id' => $request->req_id,
                'ingredient_id' => $request->ingredient_id,
            ])->increment('ingredient_qty_issued', $request->dispatch_qty);

            Stock::where([
                'id' => $request->ingredient_id,
            ])->update(['qty' => DB::raw('qty - ' . $request->dispatch_qty)]);
        }, 5);
        $user_id = $request->user_id;
        $res =   AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where(
            [
                'is_stock_updated' => '0',
                'approved' => '1',
                'user_id' => $user_id,
                'branch_id' => Parent::branch_id()
            ]
        )->whereRaw('ingredient_qty > ingredient_qty_issued')->get();
        return response()->json($res);
    }



    function update_assembly_stock_qty(Request $request)
    {
        DB::transaction(function () use ($request) {
            AssemblyProductRequisition::where(['req_id' => $request->req_id, 'stock_id' => $request->stock_id])
            ->update(['is_stock_updated' => 1,
            'final_qty_produced' => DB::raw('final_qty_produced + ' . $request->final_qty_produced)
        ]);
            Stock::where([
                'id' => $request->stock_id,
            ])->update(['qty' => DB::raw('qty + ' . $request->final_qty_produced)]);
        }, 5);
    }


    function fetchDispatchReport()
    {
        $user_id = request('user_id');
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',
            'user_id',
            'raw_material'
        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query =   DispatchAssemblyReq::with(['user',  'stock', 'issuer', 'ingredient'])->where(
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
                } else if ($column == 'raw_material') {
                    $query->whereHas('ingredient', function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', '%' . $value . '%');
                    });
                } else if ($column == 'user_id') {

                    $query->where($column, $value);
                }
            }
        });
        $raw_query->orderBy('id', 'DESC');
        if(request('page')>0){
            $res = $raw_query->paginate(10);
           }else{
            $res = $raw_query->get();
           }
        return response()->json($res);
    }





    function fetchManufactureProducts()
    {
        
        $from = date(request('from'));
        $to = date(request('to'));
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',
            'user_id',
           
        ]));
        $raw_query =   AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where(
            [

                'branch_id' => Parent::branch_id(),
                'is_stock_updated' => 1
            ]
        );
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
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('updated_at', [$from, $to]);
        });
        $raw_query->selectRaw('*,SUM(ingredient_qty_issued) AS total_ingredient_qty_issued,
        SUM(ingredient_qty-ingredient_qty_issued) AS total_ingredient_pending')->groupBy('req_id','stock_id');
       if(request('page')>0){
        $res = $raw_query->paginate(10);
       }else{
        $res = $raw_query->get();
       }
        return response()->json($res);
    }
}
