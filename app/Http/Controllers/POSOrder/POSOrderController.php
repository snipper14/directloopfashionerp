<?php

namespace App\Http\Controllers\POSOrder;

use Carbon\Carbon;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\dept\Department;
use App\Customer\Customer;
use Mike42\Escpos\Printer;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Rules\ValidateBatchNo;
use App\Rules\ValidateBatchQty;
use App\Rules\OrderAvailability;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidateExpiryOnSale;
use App\Http\Controllers\Controller;
use App\Rules\ValidateOrderComplete;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateStockAvailability;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\POS\POSOrderRequest;
use App\Http\Requests\Sale\POSSaleRequest;
use App\Http\Requests\POS\SplitOrderRequest;
use App\Rules\ValidateDeletePOSOrderPrinted;
use App\Rules\ValidateOrderStockAvailability;
use App\Rules\ValidateCrementPOSOrderPrintStatus;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class POSOrderController extends BaseController
{
    public function create(Request $request)
    {
        $validator = Validator::validate($request->all(), [


            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'order_data_offline.*.selling_price' => 'required|numeric|gt:0',
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],


        ], []);
        $branch = Auth::user()->branch; // Make sure 'branch_id' exists in the request

        // Check the enforce_customer_retail value
        $enforceCustomer = $branch && $branch->enforce_customer_retail == 1;
        if ($enforceCustomer) {
            Validator::validate($request->all(), [
                'order_data_offline.*.customer_name' => 'required',


            ], ['order_data_offline.*.customer_name.required' => 'Please select customer first',]);
        }
        DB::transaction(function () use ($request) {

            POSOrder::where('order_no', $request->order_data_offline[0]['order_no'])->delete();
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];

                // $rs = Stock::where('id', $value['stock_id'])->first();
                POSOrder::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'user_id' => $value['user_id'],
                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'selling_price' => $value['selling_price'],
                        'row_amount' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],
                        'order_no' => $value['order_no'],
                        'tax_rate' => $value['tax_rate'],
                        'product_name' => $value['product_name'],
                        'code' => $value['code'],
                        'batch_no' => $value['batch_no'],
                        'customer_id' => $request->order_data_offline[0]['customer_id'],
                        'customer_name' => $request->order_data_offline[0]['customer_name'],


                    ]
                        + Parent::branch_array()
                );
            }
        }, 5);


        return  true;
    }

    public function fetch()
    {
        $row_query = POSOrder::with([

            'stock',
            'customer',
            'user'

        ])->orderBy('id', 'desc');
        $row_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' .  request('search') . '%')
                        ->orWhere('company_phone', 'LIKE', '%' .  request('search') . '%');
                });
        });
        $row_query->selectRaw("*,SUM(qty) AS sum_qty,SUM(row_amount) AS sum_row_amount");
        if (!Parent::checkForPermission()) {
            $row_query->where('user_id', Parent::user_id());
        }
        $row_query->groupBy('order_no')->latest();
        $res = $row_query->get();
        if (request('page') > 0) {
            $res = $row_query->paginate(30);
        }
        return response()->json($res);
    }
    function fetchDetails()
    {
        $row_query = POSOrder::with([

            'stock.tax_dept'

        ])->orderBy('id', 'desc');
        $row_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%');
        });

        $row_query->where('order_no', request('order_no'))->latest();
        $res = $row_query->get();

        return response()->json($res);
    }

    function destroy(Request $request)
    {
        $order_no = $request->route('order_no');
        POSOrder::where(['order_no' => $order_no])->delete();
        return true;
    }
    public function fetchDeletedOrders()
    {
        $row_query = POSOrder::onlyTrashed()->with ([

            'stock',
            'customer',
            'user'

        ])->orderBy('id', 'desc');
        $row_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' .  request('search') . '%')
                        ->orWhere('company_phone', 'LIKE', '%' .  request('search') . '%');
                });
        });
        if(request('record')=='grouped'){
            $row_query->selectRaw("*,SUM(qty) AS sum_qty,SUM(row_amount) AS sum_row_amount");
            $row_query->groupBy('order_no')->latest();
        }else{
            $row_query->where('order_no',request('order_no'))->latest();
        }
      
       
           
      
       
        $res = $row_query->get();
        if (request('page') > 0) {
            $res = $row_query->paginate(30);
        }
        return response()->json($res);
    }
}
