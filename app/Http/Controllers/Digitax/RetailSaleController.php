<?php

namespace App\Http\Controllers\Digitax;

use Exception;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\dept\Department;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Rules\ValidateBatchNo;
use App\Rules\ValidateBatchQty;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidateExpiryOnSale;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateStockAvailability;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Sale\POSSaleRequest;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class RetailSaleController extends BaseController
{
    public function create(POSSaleRequest $request)
    {


        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'order_data_offline.*.stock_id' => [],
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request),new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],
            'order_no' => "required",

        ]);
        $url = Parent::digitaxBaseUrl() . 'stock/master';
        $stock_array=[];
        for ($i = 0; $i < count($request->order_data_offline); $i++) {
            $value = $request->order_data_offline[$i];
            $rs = Stock::where('id', $value['stock_id'])->first();
        $stock_array[]=array(
'id'=>$res->item_id,
'quantity'=>(float) $value['qty'],


        );
        }

        try {
            

            $data = [
                "item_id" => $request->item_id,
                "remaining_quantity" =>(float) $request->curr_qty,

            ];



            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);

            
            $responseData = $response->json(); 

           
            $statusCode = $response->status();


       $this->insertDB($request);
           
           
            if ($statusCode == 201) {
                return true;
            }
           else{
            return response()->json(['status' => "error", "body" => "Etims Server  error"], 500);
           }

          
        } catch (Exception $e) {
            return response()->json(['body' => $e->getMessage()], 500);
        }




    }

    function insertDB($request){
        DB::transaction(function () use ($request) {
            $department_id  =   Department::where('show_pos', 1)->first()->id;
            AllSalesTotalReport::create($request->validated() +
                ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                Parent::user_branch_id());

            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $value['order_no'],
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $value['customer_name'],
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );
                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $department_id])->decrement('qty', $value['qty']);
                }
                POSOrder::where(['order_no' => $value['order_no']])->delete();
            }
            if (Auth::user()->branch->print_type == 'server') {
                $this->printReceipt($request);
            }
        }, 5);


        return  true;
    }
    }











       
      
}
