<?php

namespace App\Http\Controllers\Sale;

use App\Sale\POSale;
use App\Stock\Stock;
use App\Sale\SoldItem;
use App\Online\OnlineOrders;
use Illuminate\Http\Request;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Sale\OnlineSaleRequest;

class OnlineSaleController extends BaseController
{
    public function create(OnlineSaleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $res = OnlineOrders::where(['order_no' => $request->order_no])->get();
            // POSale::create(['customer_id'=>1]+$request->validated() +
            //     ['cashier_id' => Parent::user_id(),'user_id' => Parent::user_id()] +
            //     Parent::branch_array() + ['waiter_id' => $res[0]->user_id]);

            AllSalesTotalReport::create($request->validated() +
                ['cashier_id' => Parent::user_id(),'user_id' => Parent::user_id(),
                'order_type' => "online"
                ] +
                Parent::branch_array() + ['waiter_id' => Parent::user_id()]);



            if (!$res->isEmpty()) {
                foreach ($res as $value) {


                    $stock_data = Stock::with(['tax_dept'])->where('id', $value['stock_id'])->first();
                    $tax_formulae = $stock_data->tax_dept->formulae;
                    $tax_amount = $value['row_total'] - ($value['row_total'] * $tax_formulae);

                    SoldItem::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],

                            'user_id' => Parent::user_id(),
                            'cashier_id' => Parent::user_id(),


                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['selling_price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $tax_amount,
                            'order_type' => "online",

                            'order_no' => $value['order_no'],
                            'cost_price' => $stock_data->cost_price,
                            'cost_total' => ($stock_data->cost_price * $value['qty']),
                            'accompaniment_id' => $value['accompaniment_id'],
                            'customer_id'=>1,
                            'location_id'=>1
                        ]
                            + Parent::branch_array()

                    );

                    AllSaleDetailsReport::create(
                        [
                            'stock_id' => $value['stock_id'],
                            "product_category_id" => Parent::product_category_id($value['stock_id']),
                            'unit_id' => $value['unit_id'],

                            'user_id' => Parent::user_id(),
                            'cashier_id' => Parent::user_id(),
                            'location_id' => 1,
                            'department_id' => 1,
                            'table_id' => 1,
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['selling_price'],
                            'row_total' => $value['row_total'],
                            'row_vat' =>  $tax_amount,
                            'order_type' => 'online',

                            'order_no' => $value['order_no'],
                            'cost_price' => $stock_data->cost_price,
                            'cost_total' => ($stock_data->cost_price * $value['qty']),
                            'accompaniment_id' => $value['accompaniment_id']
                        ]
                            + Parent::branch_array()

                    );
                }
            }


            OnlineOrders::where(['order_no' => $request->order_no])->delete();
        }, 5);


        return  true;
    }
}
