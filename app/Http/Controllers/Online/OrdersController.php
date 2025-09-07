<?php

namespace App\Http\Controllers\Online;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Online\OnlineOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Online\OrderRequest;

class OrdersController extends BaseController
{
   function completeOrder(OrderRequest $request){
    DB::transaction(function () use ($request) {
    $data = $request['order_array'];
    $order_no=$this->orderNumber();
    foreach ($data as  $value) {
      
       $insert_array=[
           'stock_id'=>$value['stock_id'],
           'unit_id'=>$value['unit_id'],
           'qty'=>$value['qty'],
           'selling_price'=>$value['selling_price'],
           'row_total'=>$value['qty']*$value['selling_price'],
           'order_date'=>Parent::currentDate(),
           'order_no'=>$order_no,
           'accompaniment_id'=>$value['accompaniment_id'],
           'customer_name'=>$request->customer_name,
           'customer_address'=>$request->customer_address,
           'customer_phone'=>$request->customer_phone,
           'email'=>$request->customer_phone,

       ];
       OnlineOrders::create($insert_array);
    }
    },5);
    return true;
   }

   function orderNumber()
    {


        $latest = OnlineOrders::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . '' . sprintf('%04d', $string);
        $isUnique = OnlineOrders::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . '' . ($latest->id + 1);
        }
        return  $orderNo;
    }

    function fetchOrders(){
        $raw_query = OnlineOrders::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('customer_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('customer_phone', 'LIKE', '%' . request('search') . '%')
                ->orWhere('customer_address', 'LIKE', '%' . request('search') . '%');
            });
        });



        
       
       
      

        $raw_query->selectRaw('*,SUM(row_total) as order_total,SUM(qty) AS sum_qty')

            ->groupBy(['order_no'])->orderBy('id', 'desc');

        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $order_array = array();
            foreach ($res as $value) {
                $data = OnlineOrders::with([
                   
                    'stock.tax_dept',
                    'unit',
                   'accompaniment',

                ])->where('order_no', $value['order_no'])->get();
                $order_array[] = array(
                    'order_no' => $value['order_no'],
                    'order_total' => $value['order_total'],
                    'sum_qty'=>$value['sum_qty'],
                    'customer_name' => $value['customer_name'],
                    'customer_address' => $value['customer_address'],
                    'customer_phone' => $value['customer_phone'],
                    'email' => $value['email'],
                    
                    'created_at' => $value['created_at'],
                   'orderPrinted' => $value['orderPrinted'],
                    "results" => $data
                );
            }

            return response()->json($order_array);
        } else {
            return response()->json($res);
        }
    }

    function updateOrderPrintStatus(Request $request){
        OnlineOrders::where('order_no',$request->order_no)->update(['orderPrinted'=>1]);
        return true;
    }

    public function destroyOrder()
    {
        DB::transaction(function () {
            $order_no = request('order_no');
            $del_res = OnlineOrders::where('order_no', $order_no)->get();
            foreach ($del_res as $value) {
                Stock::where('id', $value['stock_id'])->increment('qty', $value['qty']);
            }
            OnlineOrders::where('order_no', $order_no)->forceDelete();
        }, 5);
        return true;
    }
}
