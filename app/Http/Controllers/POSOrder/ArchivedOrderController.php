<?php

namespace App\Http\Controllers\POSOrder;

use App\POS\POSOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Online\OnlineOrders;
use App\POS\ArchivedOrder;

class ArchivedOrderController extends BaseController
{
    function create(Request $request)
    {
        DB::transaction(function () use ($request) {
            $order_no = $request->order_no;
            $res = POSOrder::where('order_no', $order_no)->get();
            foreach ($res as $value) {
                $array_rec=[
                   'branch_id'=>$value['branch_id'],
                    'user_id'=>$value['user_id'],
                    'location_id'=>$value['location_id'],
                    'table_id'=>$value['table_id'],
                    'department_id'=>$value['department_id'],
                    'stock_id'=>$value['stock_id'],
                    'unit_id'=>$value['unit_id'],
                    'customer_id'=>$value['customer_id'],
                    'qty'=>$value['qty'],
                    'price'=>$value['price'],
                    'row_total'=>$value['row_total'],
                    'row_vat'=>$value['row_vat'],
                    'no_guest'=>$value['no_guest'],
                    'notes'=>$value['notes'],
                    'order_date'=>$value['order_date'],
                    'order_no'=>$value['order_no'],
                    'prev_order_no'=>$value['prev_order_no'],
                    'merged_order_no'=>$value['merged_order_no'],
                    'isComplete'=>$value['isComplete'],
                    'order_type'=>$value['order_type'],
                    'cost_price'=>$value['cost_price'],
                    'cost_total'=>$value['cost_total'],
                    'orderPrinted'=>$value['orderPrinted'],
                    'accompaniment_id'=>$value['accompaniment_id'],
                    'isBillPrinted'=>$value['isBillPrinted'],
                   
                    'customer_name'=>$value['customer_name'],
                    'customer_address'=>$value['customer_address'],
                    'customer_phone'=>$value['customer_phone'],
                    'delivery_time'=>$value['delivery_time'],
                ];
                ArchivedOrder::create($array_rec + [
                    'archived_at' => Parent::currentDateTime(),
                    'cashier_id' => Parent::user_id()
                ]);
            }
            POSOrder::where('order_no', $order_no)->forceDelete();
        }, 5);
        return true;
    }
    

    function fetch()
    {

        $main_query = $this->query();

        $main_query->selectRaw('*,SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat')
            ->groupBy('order_no')->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }
    function fetchItemized()
    {

        $main_query = $this->query();

        $main_query->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }
    function fetchGroupedItems(){
        $main_query = $this->query();

        $main_query->
        selectRaw('*,SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat,SUM(qty) AS sum_qty')
            ->orderBy('id', 'DESC')->groupBy('stock_id');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }
    function archivedTotal()
    {
        $main_query = $this->query();

        $res =  $main_query->selectRaw('SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat')->first();


        return response()->json($res);
    }


    function query()
    {
        $from =request('from');
        $to = request('to');
        $main_query = ArchivedOrder::with([
            'table', 'location',  'stock.tax_dept',
            'unit',  'customer',  'user', 'cashier',  'department', 
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('archived_at', [$from, $to]);
        });
        $main_query->when(request('table_name', '') != '', function ($query) {
            $query->whereHas('table', function ($q) {
                $q->where('name', 'LIKE', '%' .  request('table_name') . '%');
            });
        });
        $main_query->when(request('table_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('table_id', request('table_id'));
            });
        });
        $main_query->when(request('waiter_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('user_id', request('waiter_id'));
            });
        });
        $main_query->when(request('cashier_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('cashier_id', request('cashier_id'));
            });
        });
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                });
        });
        return $main_query;
    }

    function details(){
        $order_no=request('order_no');
        $res=ArchivedOrder::with([
            'table', 'location',  'stock.tax_dept',
            'unit',  'customer',  'user', 'cashier',  'department', 'accompaniment'
        ])->where('order_no',$order_no)->get();
        return response()->json($res);
    }
}
