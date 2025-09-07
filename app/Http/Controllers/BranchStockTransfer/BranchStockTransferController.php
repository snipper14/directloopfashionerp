<?php

namespace App\Http\Controllers\BranchStockTransfer;

use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\StockTransfer\StockTransfer;
use App\Rules\ValidateStockTransferQty;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\StockTransfer\ReceiveBranchTransfer;
use App\Http\Requests\BranchStockTransfer\BranchStockTransferRequest;

class BranchStockTransferController extends BaseController
{
    public function create(BranchStockTransferRequest $request)
    {
        Validator::validate($request->all(), [

            'stock_id' => [new ValidateStockTransferQty($request)],



        ]);
        StockTransfer::create(

            $request->validated() + Parent::user_branch_id()
        );
        Inventory::where(['department_id' => $request->department_id, 'stock_id' => $request->stock_id])->decrement('qty', $request->qty);
        $res = StockTransfer::with(['stock', 'branch', 'branch_to', 'user', 'department'])->where(['status' => 'pending', 'user_id' => Parent::user_id()])->get();
        return response()->json($res);
    }
    function fetchPending()
    {
        $res = StockTransfer::with(['stock', 'branch', 'branch_to', 'user', 'department'])->where(['status' => 'pending', 'user_id' => Parent::user_id()])->latest()->get();
        return response()->json($res);
    }
    function completeTransfer()
    {
        $code = $this->generateCode();


        StockTransfer::where(['status' => 'pending'])->update(['status' => 'completed', 'transfer_code' => $code]);


        $res = StockTransfer::with(['stock', 'branch', 'branch_to', 'user', 'department'])->where(['status' => 'completed', 'transfer_code' => $code])->get();
        return response()->json($res);
    }

    function generateCode()
    {
        $res = StockTransfer::withoutGlobalScope(BranchScope::class)->where(['status' => 'completed'])->orderBy('id', 'DESC')->first();
        if ($res) {
            return ($res->transfer_code) + 1;
        } else {
            return 1;
        }
    }
    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res = StockTransfer::where('id', $request->id)->first();
            Inventory::where(['department_id' => $res->department_id, 'stock_id' => $res->stock_id])->increment('qty', $res->qty);
            StockTransfer::where('id', $request->id)->delete();
        }, 5);
        return true;
    }
    function deleteTransfer(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res = StockTransfer::where('transfer_code', $request->transfer_code)->get();
            foreach ($res as  $value) {
                Inventory::where(['department_id' => $value['department_id'], 'stock_id' => $value['stock_id']])->increment('qty', $value['qty']);
            }
            StockTransfer::where('transfer_code', $request->transfer_code)->delete();
        }, 5);
        return true;
    }
    function mainQuery()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = StockTransfer::with(['stock', 'branch_to', 'user', 'branch', 'department', 'receiver', 'received_department'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('transfer_code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('reference_code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('note', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('stock', function ($q) {
                        $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('branch_to', function ($q) {
                        $q->where('branch', 'LIKE', '%' . request('search') . '%');
                    })->orWhereHas('branch', function ($q) {
                        $q->where('branch', 'LIKE', '%' . request('search') . '%');
                    })->orWhereHas('user', function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%');
                    });;
            });
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('transfer_date', [$from, $to]);
        });
        return $main_query;
    }
    function fetch()
    {
        $main_query = $this->mainQuery();
        $main_query->orderBy('id', 'DESC')->groupBy(['transfer_code', 'branch_to_id']);
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }

    function fetchLostTransfer()
    {
        $main_query = $this->mainQuery();
        $main_query->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->where(['receive_status' => 'received', 'status' => 'completed'])
            ->whereColumn('qty', '>', 'received_qty')
            ->addSelect([
                '*', // keep all original columns
                DB::raw('(qty - received_qty) as lost_qty') // 👈 computed column
            ]);;
       
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }else{
             $data =  $main_query->get();
        }
        return response()->json($data);
    }
    function fetchReceivable()
    {
        $main_query = ReceiveBranchTransfer::with(['stock', 'user', 'branch', 'department'])->where('transfer_code', request('transfer_code'))->when(request('search', '') != '', function ($query) {})->get();
        return response()->json($main_query);
    }
    function fetchAll()
    {
        $main_query = $this->mainQuery();
        $main_query->orderBy('id', 'DESC');
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }
    function fetchDetails()
    {
        $main_query = $this->mainQuery();
        $main_query->orderBy('id', 'DESC')->where(['transfer_code' => request('transfer_code')]);
        $data =  $main_query->get();

        return response()->json($data);
    }
    function fetchUnreceivedTransfer()
    {
        $main_query = $this->mainQuery();
        $main_query->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->where(['receive_status' => 'pending', 'status' => 'completed', 'branch_to_id' => Parent::branch_id(), 'transfer_code' => request('transfer_code')]);
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }
    function fetchUnreceivedGroupedTransfer()
    {
        $main_query = $this->mainQuery();
        $main_query->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->groupBy('transfer_code')->where(['receive_status' => 'pending', 'status' => 'completed', 'branch_to_id' => Parent::branch_id()]);
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }

    function receiveTransfer(Request $request)
    {
        Validator::validate($request->all(), [

            'received_department_id' => 'required',
        ], ['received_department_id.required' => "Select location"]);
        DB::transaction(function () use ($request) {

            DB::enableQueryLog();
            StockTransfer::withoutGlobalScope(BranchScope::class)->where(['id' => $request->id])->update(
                [
                    'receive_status' => 'received',
                    'received_department_id' => $request->received_department_id,
                    'receiver_id' => Parent::user_id(),
                    'received_date' => $request->received_date,
                    'received_qty' => $request->qty
                ]
            );
            $r = StockTransfer::withoutGlobalScope(BranchScope::class)->where(['id' => $request->id])->first();

            ReceiveBranchTransfer::create(
                [

                    'department_id' => $request->received_department_id,
                    'stock_id' => $request->stock_id,
                    'received_date' => $request->received_date,
                    'qty' => $request->qty,
                    'transfer_code' => $r->transfer_code
                ] + Parent::user_branch_id()
            );
            Inventory::updateOrCreate(
                ['department_id' => $request->received_department_id, 'stock_id' => $request->stock_id, 'branch_id' => Parent::branch_id()],
                ['qty' => DB::raw('qty +' . $request->qty)]
            );
        }, 5);
    }
    function receiveAll(Request $request)
    {
        Validator::validate($request->all(), [

            'received_department_id' => 'required',
        ], ['received_department_id.required' => "Select location"]);
        DB::transaction(function () use ($request) {

            for ($i = 0; $i < count($request->transfer_data); $i++) {
                $value = $request->transfer_data[$i];
                StockTransfer::withoutGlobalScope(BranchScope::class)->where(['id' => $value['id']])->update(
                    [
                        'receive_status' => 'received',
                        'received_department_id' => $request->received_department_id,
                        'receiver_id' => Parent::user_id(),
                        'received_date' => $request->received_date,
                        'received_qty' => $value['qty']
                    ]
                );
                $re = StockTransfer::withoutGlobalScope(BranchScope::class)->where(['id' => $value['id']])->first();

                ReceiveBranchTransfer::create(
                    [

                        'department_id' => $request->received_department_id,
                        'stock_id' => $re->stock_id,
                        'received_date' => $request->received_date,
                        'qty' =>  $value['qty'],
                        'transfer_code' => $re->transfer_code
                    ] + Parent::user_branch_id()
                );
                Inventory::updateOrCreate(
                    ['department_id' => $request->received_department_id, 'stock_id' => $value['stock_id'], 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $value['qty'])]
                );
            }
        }, 5);
        return true;
    }

    function fetchReceived()
    {
        $from = date(request('r_from'));
        $to = date(request('r_to'));
        $main_query = $this->mainQuery();
        $main_query->when($from != '' && $to != '', function ($query) use ($from, $to) {

            $query->whereBetween('received_date', [$from, $to]);
        })->groupBy('transfer_code');
        $main_query->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->where(['receive_status' => 'received',  'branch_to_id' => Parent::branch_id()]);
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }
    function viewDetails()
    {
        $from = date(request('r_from'));
        $to = date(request('r_to'));
        $main_query = $this->mainQuery();
        $main_query->when($from != '' && $to != '', function ($query) use ($from, $to) {

            $query->whereBetween('received_date', [$from, $to]);
        })->where('transfer_code', request('transfer_code'));
        $main_query->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->where(['receive_status' => 'received',  'branch_to_id' => Parent::branch_id()]);
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }
}
