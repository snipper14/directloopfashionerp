<?php

namespace App\Http\Controllers\Sale;

use App\Sale\Sale;
use Carbon\Carbon;
use App\Stock\Stock;
use App\Cart\OrderCart;
use Illuminate\Http\Request;
use App\Rules\OrderAvailability;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartRequest;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Cart\SaleRequest;
use App\Inventory\Inventory;
use App\Sale\AllSaleDetailsReport;
use App\Sale\AllSalesTotalReport;

class SaleController extends BaseController
{
    //



    public function create(Request $request)
    {
        $results = OrderCart::where(['customer_id' => $request->id, 'branch_id' => Parent::branch_id()])->get();
        if (!$results->isEmpty()) {
            foreach ($results as $value) {
                $id = $value['id'];

                $id = OrderCart::find($id);
                $id->delete();
                Sale::create([
                    "stock_id" => $value['stock_id'],
                    'customer_id' => $value['customer_id'],
                    'department_id' => $value['department_id'],
                    'product_category_id' => $value['product_category_id'],
                    'discount' => $value['discount'],
                    'purchase_price' => $value['purchase_price'],
                    'order_date' => $value['order_date'],
                    'order_no' => $value['order_no'],
                    'qty' => $value['qty'],
                    'item_price' => $value['selling_price'],
                    'row_total' => $value['row_total'],
                    'order_total' => $request->grand_total,
                    'tax_amount' => $value['tax_amount'],
                    'batch_no' => $value['batch_no'],

                ] + Parent::user_branch_id());
            }
            return true;
        } else {

            return true;
        }
    }
    public function fetch()
    {
        $data = Sale::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('customer', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('Product', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['stock', 'customer'])->groupBy('order_no')->where(['branch_id' => Parent::branch_id()])->selectRaw('*, sum(qty) as sum_qty')->orderBy('id', 'DESC')->paginate(10);

        return response()->json(['results' => $data]);
    }

    function orderNumber(Request $request)
    {
        $id = $request->route('id');
        $res = OrderCart::where('customer_id', $id)->first();
        if ($res) {
            return response()->json(['result' => $res->order_no]);
        }
        $latest = Sale::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'ORN' . sprintf('%04d', $string);
        $isUnique = Sale::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'ORN' . ((int)$latest->id + 1);
        }
        return  response()->json(['result' => $orderNo . Parent::user_id()]);
    }

    public function orders(Request $request)
    {
        $res = Sale::with(['stock', 'customer'])->where(['order_no' => $request->order_no, 'customer_id' => $request->customer_id, 'branch_id' => Parent::branch_id()])->get();
        return response()->json(["results" => $res]);
    }

    public function updateOrder(SaleRequest $request)
    {
        $this->validate($request, ['qty' => new OrderAvailability($request->stock_id, $request->department_id)]);

        $isItemsInCart = Sale::where([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,

            'order_no' => $request->order_no,
        ])->get();


        $data = null;
        if (!$isItemsInCart->isEmpty()) {
            if (Parent::isInventory($request->stock_id)) {
                Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->increment('qty', $isItemsInCart[0]['qty']);
            }
            Sale::where([
                'stock_id' => $request->stock_id, 'customer_id' => $request->customer_id,
                'order_no' => $request->order_no
            ])->delete();
        }
        DB::enableQueryLog();
        Sale::updateOrCreate(
            [
                'stock_id' => $request->stock_id, 'order_no' => $request->order_no, 'customer_id' => $request->customer_id,
                'branch_id' => Parent::branch_id()
            ],
            [
                'order_no' => $request->order_no,
                'user_id' => Parent::user_id(),
                'department_id' => $request->department_id,
                'product_category_id' => $request->product_category_id,
                'order_date' => $request->order_date,
                'item_price' => $request->item_price,
                'purchase_price' => $request->purchase_price,
                'discount' => $request->discount,
                'qty' => DB::raw('qty + ' . $request->qty),
                'row_total' => DB::raw('row_total + ' . $request->row_total),
                'tax_amount' => DB::raw('tax_amount + ' . $request->tax_amount)
            ]
        );
        //  print_r(DB::getQueryLog());
        if (Parent::isInventory($request->stock_id)) {
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->decrement('qty', $request->qty);
        }
        Sale::where(['order_no' => $request->order_no, 'customer_id' => $request->customer_id])
            ->update([
                'order_total' => $request->order_total,
                'order_date' => $request->order_date
            ]);
        $res = Sale::with(['stock', 'customer'])->where(['order_no' => $request->order_no, 'customer_id' => $request->customer_id, 'branch_id' => Parent::branch_id()])->get();
        return response()->json($res);
    }

    public function cancelOrders(Request $request)
    {
        $results = Sale::where([

            'customer_id' => $request->customer_id,

            'order_no' => $request->order_no
        ])->get();
        if (!$results->isEmpty()) {
            foreach ($results as $value) {
                $id = $value['id'];

                Sale::find($id)->delete();

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::where([
                        'stock_id' => $value['stock_id'],
                        'department_id' => $value['department_id'],

                    ])
                        ->increment('qty', $value['qty']);
                }
            }
            return true;
        } else {

            return true;
        }
    }
    public function deleteOrderItem(Request $request)
    {

        $order_no = $request->id;
        $order_no = Sale::find($order_no);
        $data = $order_no->delete();
        if (Parent::isInventory($request->stock_id)) {
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->increment('qty', $request->qty);
        }
        Sale::where(['order_no' => $request->order_no])
            ->decrement('order_total', $request->row_total);
        return response()->json(['results' => $data]);
    }

    public function fetchSaleDetails()
    {
        //    DB::enableQueryLog();
        $main_query = AllSalesTotalReport::with(['user', 'cashier'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('receipt_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('receipt_total', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('cashier', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });


        if (!$this->checkForPermission()) {

           $main_query->where(['user_id' => Parent::user_id()]);
        }

        $main_query->selectRaw('*
    ,SUM(receipt_total) AS total
    ,SUM(cash_pay) AS total_cash_pay
    ,SUM(card_pay) AS total_card_pay
    ,SUM(mpesa_pay) AS total_mpesa_pay
    
    ,SUM(total_vat) AS vat_total,
    SUM(receipt_balance) AS total_receipt_balance');

        $res = $main_query->where([
            'branch_id' => Parent::branch_id(),
            'paid_date' => request('paid_date')
        ])->orderBy('id', 'DESC')->groupBy('receipt_no')->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(15);
        }
        return response()->json($res);
    }

    public function fetchSaleDetailsWithItems()
    {
        //    DB::enableQueryLog();
        $main_query = AllSaleDetailsReport::with(['user', 'cashier','stock'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%');
                })->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                 
            });
        });


     

        $main_query->selectRaw('*,SUM(qty) AS sum_qty
    ,SUM(row_total) AS sum_total_sales
    ,SUM(row_vat) AS totat_vat
    ,SUM(cost_total) AS total_purchase_price,
     (SUM(row_total) - SUM(cost_total)) AS sum_profit');
        

        $main_query->where([
            'branch_id' => Parent::branch_id()
          
        ])
        ->whereDate('created_at', request('paid_date'))

       ->groupBy('stock_id')
        
        
        ->when(request('sort_sum_qty'), function ($query) {
            $query->orderBy('sum_qty', request('sort_sum_qty'));
        })
        ->when(request('sort_sum_total_sales'), function ($query) {
            $query->orderBy('sum_total_sales', request('sort_sum_total_sales'));
        })
        ->when(request('sort_sum_profit'), function ($query) {
            $query->orderBy('sum_profit', request('sort_sum_profit'));
        })
        ->when(request('sort_total_purchase_price'), function ($query) {
            $query->orderBy('total_purchase_price', request('sort_total_purchase_price'));
        });
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(45);
        }
        return response()->json($res);
    }

    function fetchSoldReceiptOrder()
    {
        DB::enableQueryLog();
        $main_query = AllSaleDetailsReport::with(['stock.tax_dept', 'user'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('receipt_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('receipt_total', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('cashier', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });



        $res = $main_query->where([
            
            'order_no' => request('order_no')
        ])->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(15);
        }
       
        return response()->json($res);
    }
}
