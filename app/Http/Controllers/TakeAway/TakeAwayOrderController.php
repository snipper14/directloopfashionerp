<?php

namespace App\Http\Controllers\TakeAway;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\TakeAway\TakeawayOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Orders\TakeAwayOrderRequest;

class TakeAwayOrderController extends BaseController
{
    public function create(TakeAwayOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $customer_id = $this->checkDefaultCustomer($request);
            if ($this->isItemInCart($request)) {
                TakeawayOrder::where(['stock_id' => $request->stock_id, 'order_no' => $request->order_no])
                    ->update(array(
                        'qty' => DB::raw('qty + ' . $request->qty),
                        'row_total' => DB::raw('row_total + ' . $request->row_total),
                        'row_vat' => DB::raw('row_vat + ' . $request->row_vat),
                        'cost_total' => DB::raw('cost_total + ' . $request->cost_total),

                    ));
            } else {
                TakeawayOrder::create(['customer_id' => $customer_id] + $request->validated() + Parent::user_branch_id());
            }

            Stock::where(['id' => $request->stock_id])->decrement('qty', $request->qty);
        }, 5);
        $res = TakeawayOrder::with([
            'department',
            'stock.tax_dept',
            'unit',
            'customer',
            'user'
        ])->where(['order_no' => $request->order_no, 'isComplete' => '0'] + Parent::branch_array())->orderBy('id', 'desc')->get();

        return response()->json($res);
    }

    public function isItemInCart(Request $request)
    {
        $res = TakeawayOrder::where([
            'stock_id' => $request->stock_id,
            'order_no' => $request->order_no,

        ] + Parent::branch_array())->first();

        return  $res ? true : false;
    }
    function checkDefaultCustomer($request)
    {
        $customer = $request->company_name;
        $customer_id = null;
        if ($customer == 'Walk in') {
            $res =  Customer::where('company_name', $customer)->first();
            if ($res) {
                $customer_id = $res->id;
            } else {
                $data =    Customer::create(['company_name' => $customer] + Parent::branch_array());
                $customer_id =  $data->id;
            }
        } else {
            $customer_id = $request->customer_id;
        }
        return $customer_id;
    }


    public function getPendingOrders(Request $request)
    {
        $order_no = $request->order_no;
        $raw_query = TakeawayOrder::with([
            'department',
            'stock.tax_dept',
            'unit',
            'customer',
            'user'
        ])->where(Parent::user_branch_id())->orderBy('id', 'desc');

        if ($order_no != '') {
            $raw_query->where('order_no', $order_no);
            $res = $raw_query->get();
            return response()->json(['status' => "orders", 'results' => $res]);
        }
        return response()->json(['status' => "order_no", 'results' => $this->orderNumber()]);
    }

    function orderNumber()
    {


        $latest = TakeawayOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . '' . sprintf('%04d', $string);
        $isUnique = TakeawayOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . '' . ($latest->id + 1);
        }
        return  $orderNo . Parent::user_id();
    }

    public function crementOrder(Request $request)
    {

        if ($request->update_type == 'add') {

            TakeAwayOrder::where(['stock_id' => $request->stock_id, 'order_no' => $request->order_no])
                ->update(array(
                    'qty' => DB::raw('qty + ' . $request->qty),
                    'row_total' => DB::raw('row_total + ' . $request->row_total),
                    'cost_total' => DB::raw('cost_total + ' . $request->cost_total),
                    'row_vat' => DB::raw('row_vat + ' . $request->row_vat)
                ));
            Stock::where(['id' => $request->stock_id])->decrement('qty', $request->qty);
        } else if ($request->update_type == 'minus') {
            TakeAwayOrder::where(['stock_id' => $request->stock_id, 'order_no' => $request->order_no])
                ->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' . $request->row_total),
                    'row_vat' => DB::raw('row_vat - ' . $request->row_vat),
                    'cost_total' => DB::raw('cost_total - ' . $request->cost_total),
                ));
            Stock::where(['id' => $request->stock_id])->increment('qty', $request->qty);
        }

        return response()->json($this->fetchByOrderNo($request->order_no));
    }


    public function destroy()
    {
        DB::transaction(function () {
            $id = request('id');
            $del_res = TakeawayOrder::where('id', $id)->get();
            foreach ($del_res as $value) {
                Stock::where('id', $value['stock_id'])->increment('qty', $value['qty']);
            }
            TakeawayOrder::where('id', $id)->delete();
        }, 5);
        return true;
    }
    public function destroyOrder()
    {
        DB::transaction(function () {
            $order_no = request('order_no');
            $del_res = TakeawayOrder::where('order_no', $order_no)->get();
            foreach ($del_res as $value) {
                Stock::where('id', $value['stock_id'])->increment('qty', $value['qty']);
            }
            TakeawayOrder::where('order_no', $order_no)->delete();
        }, 5);
        return true;
    }

    public function completeOrder(Request $request)
    {
        $order_no = $request->order_no;
        TakeawayOrder::where(['order_no' => $order_no])->update(['isComplete' => '1']);
        return true;
    }
    public function addNote(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'notes' => 'required',
            'order_no' => 'required',
        ],  [
            'id.required' => 'You have to choose cart  item first!',

        ]);
        TakeawayOrder::where('id', $request->id)->update(['notes' => $request->notes]);
        return response()->json($this->fetchByOrderNo($request->order_no));
    }
    function fetchByOrderNo($order_no)
    {
        $res = TakeawayOrder::with([
            'department',
            'stock.tax_dept',
            'unit',
            'customer',
            'user',
        ])->where([
            'order_no' => $order_no,
            'isComplete' => "0",

        ] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return $res;
    }
    public function validateBill(Request $request)
    {
        $res = TakeawayOrder::where('order_no', $request->order_no)->first()->isComplete;
        return response()->json($res);
    }

    public function fetchBills(Request $request)
    {

        $raw_query = TakeawayOrder::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%');
            });
        });




        $raw_query->where(
            Parent::branch_array()
        );
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }

        $raw_query->selectRaw('*,SUM(row_total) as order_total,SUM(row_vat) AS total_tax')

            ->groupBy(['order_no'])->orderBy('id', 'desc');

        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $order_array = array();
            foreach ($res as $value) {
                $data = TakeawayOrder::with([
                    'department',
                    'stock.tax_dept',
                    'unit',
                    'customer',
                    'user'
                ])->where('order_no', $value['order_no'])->get();
                $order_array[] = array(
                    'order_no' => $value['order_no'],
                    'order_total' => $value['order_total'],

                    'total_tax' => $value['total_tax'],
                    "results" => $data
                );
            }

            return response()->json($order_array);
        } else {
            return response()->json($res);
        }
    }

    function recallBill()
    {
        $order_no = request('order_no');
        TakeawayOrder::where('order_no', $order_no)->update(['isComplete' => '0']);
        return true;
    }

    function updateOrderCustomer(Request $request)
    {
        TakeawayOrder::where('order_no', $request->order_no)->update(['customer_id' => $request->customer_id]);

        return response()->json($this->fetchByOrderNo($request->order_no));
    }


    function fetchOrders()
    {
        $main_query = TakeawayOrder::with([
            'department',
            'stock.tax_dept',
            'unit',
            'customer',
            'user',
        ]);




        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' .  request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->selectRaw('*,SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat')
            ->groupBy('order_no')->orderBy('id', 'ASC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }

    function getByOrder()
    {
        $order_no = request('order_no');
        $res =   TakeawayOrder::with([
            'department',
            'stock.tax_dept',
            'unit',
            'customer',
            'user',
        ])->where('order_no', $order_no)->get();
        return response()->json($res);
    }
}
