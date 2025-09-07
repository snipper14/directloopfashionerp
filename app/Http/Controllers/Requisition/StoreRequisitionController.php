<?php

namespace App\Http\Controllers\Requisition;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Requistion\Requistion;
use App\Stock\InitialStoreIssue;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateMainStoreStockQtyAvailability;
use App\Http\Requests\Requisition\StoreRequisitionRequest;
use App\Stock\Stock;

class StoreRequisitionController extends BaseController
{

    function create(StoreRequisitionRequest $request)
    {

        Requistion::updateOrCreate(['req_id' => $request->req_id, 'stock_id' => $request->stock_id], $request->validated() + Parent::user_branch_id());
        $res = Requistion::with(['stock'])->where(['req_id' => $request->req_id])->get();
        return response()->json($res);
    }
    function fetchPendngReq()
    {
        $res = Requistion::with(['user', 'department', 'stock',])->where([

            'status' => "progress",

        ] + Parent::user_branch_id())->get();

        return response()->json(['pending_req' => $res, 'req_no' => $this->requisitionNo()]);
    }
    function requisitionNo()
    {
        $latest = Requistion::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'RQ' . sprintf('%04d', $string);
        $isUnique = Requistion::where('req_id', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'RQ' . ($latest->id + 1);
        }
        return  $req_no;
    }

    function updateReqData(StoreRequisitionRequest $request)
    {
        Requistion::where([
            'id' => $request->id
        ])->update($request->validated());
        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'req_id' => $request->req_id,
        ] + Parent::user_branch_id())->get();
        return response()->json($res);
    }
    function deleteItem(Request $request)
    {
        $id = $request->route('id');
        Requistion::find($id)->delete();
        return true;
    }
    function destroy(Request $request)
    {
        $id = $request->route('req_id');
        Requistion::where('req_id', $id)->delete();
        return true;
    }

    function completeReq(Request $request)
    {

        Requistion::where([
            'req_id' => $request->req_id,
        ])->update(['status' => 'complete']);


        return response()->json(['req_no' => $this->requisitionNo()]);
    }


    function fetch()
    {

        $raw_query = Requistion::with(['user', 'department', 'stock.unit',])->where([
            'branch_id' => Parent::branch_id(),

            'status' => "complete",

        ])
            ->whereColumn('qty_issued', '<', 'qty');
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('req_id', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_due', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
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
    function report()
    {

        $raw_query = Requistion::with(['user', 'department', 'stock.unit',])->where([
            'branch_id' => Parent::branch_id(),

            'status' => "complete",

        ]);
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('req_id', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_due', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
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

    function issueItem(Request $request)
    {
        $this->validate($request, [
            'stock_id' => new ValidateMainStoreStockQtyAvailability($request->qty),

        ]);

        DB::transaction(function () use ($request) {
            Requistion::where(['req_id' => $request->req_id, 'stock_id' => $request->stock_id])->update([
                'status' => 'complete',
                'qty_issued' => $request->qty
            ]);
            Stock::where('id', $request->stock_id)->decrement('main_store_qty', $request->qty);
            InitialStoreIssue::create(
                [
                    'issue_no' => $request->req_id,
                    'stock_id' => $request->stock_id,
                    'unit_id' => $request->stock['unit_id'],
                    'status' => 'complete',
                    'qty_issued' => $request->qty,
                    'row_total' => ($request->qty * $request->stock['purchase_price']),
                    'unit' => $request->stock['unit']['name'],
                    'date_issued' => Parent::currentDate(),
                    'purchase_price' => $request->stock['purchase_price']

                ] + Parent::user_branch_id()
            );
        }, 5);
        return true;
    }
}
