<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Branch\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\PurchaseOrder\DirectPOReceivable;
use App\PurchaseOrder\DirectPurchaseOrder;
use App\Http\Requests\Purchase\DirectPurchaseRequest;
use App\Stock\StockMovt;
use Barryvdh\DomPDF\Facade as PDF;

class DirectPurchaseController extends BaseController
{
    function create(DirectPurchaseRequest $request)
    {

        $prev_store_qty = Stock::where('id', $request->stock_id)->first()->main_store_qty;
        DirectPOReceivable::create(['opening_stock' => $prev_store_qty] + $request->validated() + Parent::user_branch_id());

        $res = DirectPOReceivable::with(['stock'])->where([
            'order_no' => $request->order_no,

            'status' => 'pending'
        ])->get();
        return response()->json($res);
    }

    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            // PurchaseOrder::where('id', $request->direct_purchase_order_id)->decrement('qty_delivered', $request->qty_delivered);
            DirectPOReceivable::find($request->id)->delete();
        }, 5);
        return true;
    }
    function fetchPendingDelivery(Request $request)
    {
        $res = DirectPOReceivable::with(['stock'])->where([
            'order_no' => request('order_no'),
            'status' => 'pending'

        ])->get();
        return response()->json($res);
    }
    function fetchDeliveryNotes()
    {
        $filled = array_filter(request()->only([

            'order_no',
            'delivery_no'
        ]));
        $raw_query = DirectPOReceivable::with(['purchase_order', 'purchase_order.branch', 'stock', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'user_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%');


                // ->orWhereHas(
                //     'user',
                //     function ($query) {
                //         $query->where('name', 'LIKE', '%' . request('search') . '%');
                //     }
                // );
            });
        });

        $raw_query->where(['status' => 'completed'] + Parent::branch_array())->groupBy('delivery_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(qty_delivered) AS sum_qty_delivered,SUM(qty_ordered) AS sum_qty_ordered');
        $res = $raw_query->paginate(40);
        return response()->json($res);
    }
    function queryDPOrderReceivable()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = DirectPOReceivable::with(['supplier', 'direct_purchase_order', 'stock.unit', 'branch', 'user']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('received_date', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas(
                        'stock',
                        function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });
        $raw_query->latest('created_at');
        if (request('page') > 0) {
            $res = $raw_query->paginate(25);
        } else {
            $res = $raw_query->get();
        }
        return $res;
    }

    function fetchDPOrderReceivable()
    {

        return response()->json($this->queryDPOrderReceivable());
    }
    function completeRecevable(Request $request)
    {

        DirectPOReceivable::where([
            'order_no' => $request->order_no,
        ])->update(['status' => 'completed']);
        return true;
    }

    function deliveryNo()
    {


        $latest = DirectPOReceivable::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $delivery_no = $today . '-' . 'DN' . sprintf('%04d', $string);
        $isUnique = DirectPOReceivable::where('delivery_no', $delivery_no)->first();
        if ($isUnique) {
            $delivery_no = $today . '-' . 'DODN' . ($latest->id + 1);
        }
        return  $delivery_no;
    }
    function checkPendingDeliveryNo()
    {
        $order_no = request('order_no');
        $query = DirectPOReceivable::where(['order_no' => $order_no, 'status' => 'pending'])->first();
        if ($query) {
            return $query->delivery_no;
        } else {
            return false;
        }
    }
    function fetchDeliveryNo()
    {

        if ($this->checkPendingDeliveryNo()) {
            return response()->json($this->checkPendingDeliveryNo());
        } else {
            return response()->json($this->deliveryNo());
        }
    }
    function fetchOrders()
    {

        $filled = array_filter(request()->only([
            'user_id',
            'order_no',

        ]));
        $raw_query =  DirectPurchaseOrder::with(['branch', 'stock', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'user_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('order_deadline', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('progress', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('internal_confirmation', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(['internal_confirmation' => "confirmed"] + Parent::branch_array())
            ->whereRaw('qty > qty_delivered')
            ->groupBy('order_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty,
    SUM(qty_delivered) AS total_qty_delivered');
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }


    function downloadDN(Request $request)
    {


        $order_no = $request->order_no;

        DB::transaction(function () use ($request) {

            $update_data = DirectPOReceivable::where(
                ['status' => "pending", 'delivery_no' => $request->delivery_no]
            )
                ->get();

            foreach ($update_data as $value) {
                Stock::where(['id' => $value['stock_id']])->increment('main_store_qty', $value['qty_delivered']);
                Stock::where(['id' => $value['stock_id']])->update(['purchase_price' => $value['unit_price']]);

                DirectPurchaseOrder::where('id', $value['direct_purchase_order_id'])->increment('qty_delivered', $value['qty_delivered']);
                $current_store_qty = Stock::where('id', $value['stock_id'])->first()->main_store_qty;
                StockMovt::create([
                    "opening_stock" => $value['opening_stock'],
                    'grn_qty' => $value['qty_delivered'],
                    'stock_id' => $value['stock_id'],
                    'date_recorded' => Parent::currentDate(),
                    'system_qty' => $current_store_qty,
                ] + Parent::user_branch_id());
            }

            DirectPOReceivable::where([
                'order_no' =>  $request->order_no,
                'delivery_no' => $request->delivery_no
            ])->update(['status' => 'completed',]);
        }, 5);
        $branch = Branch::where('id', Parent::branch_id())->first();
        $res =  DirectPOReceivable::with(['stock', 'user.department', 'direct_purchase_order', 'direct_purchase_order.branch'])->where([
            'order_no' => $order_no,
            'delivery_no' => $request->delivery_no,

        ])->get();
        
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }

        $path = public_path() . '/pdf/' . "internal_grn" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, 'branch' => $branch];
        $pdf = PDF::loadView('pdf.direct_delivery', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }

    function receiveAll(Request $request)
    {


        $order_no = $request->order_no;
        $order_res = DirectPurchaseOrder::where(['order_no' => $order_no,])->whereRaw('qty > qty_delivered')->get();
        if (!$order_res->isEmpty()) {
            foreach ($order_res as  $value) {
                DirectPOReceivable::create([
                    'direct_purchase_order_id' => $value['id'],
                    'stock_id' => $value['stock_id'],
                    'qty_delivered' => ($value['qty'] - $value['qty_delivered']),
                    'qty_ordered' => $value['qty'],
                    'order_no' => $value['order_no'],
                    'delivery_no' => $request->delivery_no,
                    'received_date' => Parent::currentDate(),
                    'sub_total' => ($value['unit_price'] * ($value['qty'] - $value['qty_delivered'])),
                    'unit_price' => $value['unit_price'],
                    'status' => "pending"
                ] + Parent::user_branch_id());
            }
        }


        DB::transaction(function () use ($request) {

            $update_data = DirectPOReceivable::where(
                ['status' => "pending", 'delivery_no' => $request->delivery_no]
            )
                ->get();

            foreach ($update_data as $value) {
                Stock::where(['id' => $value['stock_id']])->increment('qty', $value['qty_delivered']);
                DirectPurchaseOrder::where('id', $value['direct_purchase_order_id'])->increment('qty_delivered', $value['qty_delivered']);
            }

            DirectPOReceivable::where([
                'order_no' =>  $request->order_no,
                'delivery_no' => $request->delivery_no
            ])->update(['status' => 'completed',]);
        }, 5);
        $branch = Branch::where('id', Parent::branch_id())->first();
        $res =  DirectPOReceivable::with(['stock', 'user.department', 'direct_purchase_order', 'direct_purchase_order.branch'])->where([
            'order_no' => $order_no,
            'delivery_no' => $request->delivery_no,

        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }

        $path = public_path() . '/pdf/' . $res[0]->delivery_no . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, 'branch' => $branch];
        $pdf = PDF::loadView('pdf.direct_delivery', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }

    function downloadDetailsPdf()
    {
        ini_set('max_execution_time', 600);

        $res = $this->queryDPOrderReceivable();

        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "details" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];
        $pdf = PDF::loadView('pdf.direct_purchase_report', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }

    function fetchDirectPurchaseGRN()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = DirectPOReceivable::with(['supplier', 'direct_purchase_order', 'stock.unit', 'branch', 'user']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('received_date', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });
        $raw_query->where(['status' => "completed"])->groupBy('delivery_no')->selectRaw("*,
        SUM(qty_delivered) AS sum_qty_delivered,
        SUM(qty_ordered) AS sum_qty_ordered,
        SUM(sub_total) AS sum_sub_total

        ");

        $raw_query->latest('created_at');
        if (request('page') > 0) {
            $res = $raw_query->paginate(25);
        } else {
            $res = $raw_query->get();
        }
        return response()->json($res);
    }
}
