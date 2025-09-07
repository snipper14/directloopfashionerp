<?php

namespace App\Http\Controllers\Stock;

use App\Stock\IssueHome;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\HomeIssueRequest;
use App\Rules\CheckMainStoreStockQtyAvailability;
use App\Stock\Stock;

class HomeIssueController extends BaseController
{

    function create(HomeIssueRequest $request){
        $this->validate($request, ['stock_id' => new CheckMainStoreStockQtyAvailability($request->qty)]);
        IssueHome::create($request->validated()+['user_id'=>Parent::user_id()]);
        Stock::where(['id'=>$request->stock_id])->decrement('main_store_qty',$request->qty);
        return true;

    }
    //
    function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = IssueHome::with(['stock', 'user']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_issued', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
               $q-> whereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'stock',
                        function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });
        $raw_query->orderBy('id', 'DESC');
       
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }

    function destroy(Request $request){
        IssueHome::where('id',$request->id)->delete();
        Stock::where('id',$request->stock_id)->increment('main_store_qty',$request->qty);
        return true;
    }
}
