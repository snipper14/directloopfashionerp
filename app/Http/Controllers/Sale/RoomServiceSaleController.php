<?php

namespace App\Http\Controllers\Sale;

use App\Sale\Sale;
use Carbon\Carbon;
use App\Guest\Guest;
use App\POS\RoomOrder;
use App\Cart\OrderCart;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\Sale\RoomServiceSale;
use App\Transaction\Transaction;
use App\Sale\AllSalesTotalReport;
use App\Sale\RoomServiceSoldItem;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Sale\RoomServiceSaleRequest;

class RoomServiceSaleController extends BaseController
{
    public function create(RoomServiceSaleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $res = RoomOrder::where(['order_no' => $request->order_no])->get();
            $res_room_service_sale = RoomServiceSale::create($request->validated() +
                ['cashier_id' => Parent::user_id()] +
                Parent::branch_array() + ['waiter_id' => $res[0]->user_id]);

            AllSalesTotalReport::create($request->validated() +
                ['cashier_id' => Parent::user_id()] +
                Parent::branch_array() + ['waiter_id' => $res[0]->user_id]);


            $customer_name = Guest::where('id', $request->guest_id)->first()->name;
            $insert_ledger_data = array(


                'ref' => $request->receipt_no,
                'entry_date' => $request->paid_date,
                'description' => 'room service sales',
                'credit' => "Stock",
                'debit' => $customer_name,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    RoomServiceSoldItem::create(
                        [
                            'order_no' => $value['order_no'],
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'department_id' => $value['department_id'],
                            'guest_id' => $value['guest_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id(),
                            'room_id' => $value['room_id'],
                            'room_reservation_id' => $value['room_reservation_id'],
                            'room_package_id' => $value['room_package_id'],
                            'room_service_sale_id' => $res_room_service_sale->id,
                            'order_date' => $value['order_date'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'vat_rate' => $value['vat_rate'],
                            'row_vat' => $value['row_vat'],
                            'no_guest' => $value['no_guest'],
                            'service_charge_amount' => $value['service_charge_amount'],
                            'service_charge_rate' => $value['service_charge_rate'],
                            'order_type' => $value['order_type'],
                            'prev_order_no' => $value['prev_order_no'],
                            'accompaniment_id' => $value['accompaniment_id'],
                        ]
                            + Parent::branch_array()
                    );



                    AllSaleDetailsReport::create(
                        [
                            'stock_id' => $value['stock_id'],
                            "product_category_id" => Parent::product_category_id($value['stock_id']),
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'location_id' => $value['location_id'],
                            'department_id' => $value['department_id'],
                            'table_id' => $value['table_id'],
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],

                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'accompaniment_id' => $value['accompaniment_id']
                        ]
                            + Parent::branch_array()

                    );
                }
            }


            RoomOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }



    public function salesReport()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RoomServiceSale::with([
            'customer', 'user', 'cashier', 'guest'

        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('paid_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array())->latest('created_at');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        }
        return response()->json($res);
    }
    public function salesReportTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RoomServiceSale::with([
            'customer', 'user', 'cashier', 'guest'
        ]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('paid_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array())->selectRaw('*,
        SUM(receipt_total) AS sum_receipt_total,
        SUM(cash_pay+receipt_balance) AS sum_cash_pay,
        SUM(mpesa_pay) AS sum_mpesa_pay,
        SUM(card_pay) AS sum_card_pay,
        SUM(credit) AS sum_credit_pay,
        SUM(total_vat) AS sum_total_vat,
        SUM(service_charge_total) AS sum_service_charge_total
        ');
        $res = $main_query->first();

        return response()->json($res);
    }

    function getSaleDetails()
    {
        $order_no = request('order_no');
        $res = RoomServiceSoldItem::with([
            'room',
            'room_package', 'stock', 'unit', 'guest', 'user', 'cashier', 'department', 'accompaniment'
        ])->where('order_no', $order_no)->get();

        return response()->json($res);
    }
    public function detailsReport()
    {
        ini_set('max_execution_time', 2400);
        $filled = array_filter(request()->only([
            'room_id',
            'room_package_id',

        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RoomServiceSoldItem::with([
            'room', 'department',
            'room_package', 'stock', 'unit', 'guest', 'user', 'cashier', 'accompaniment'
        ]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });
        $main_query->when(count($filled) > 0, function ($query) use ($filled) {

            foreach ($filled as $column => $value) {
                $query->where($column,  $value);
            }
        });

        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where(Parent::branch_array());
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(100);
        }
        return response()->json($res);
    }


    public function  soldItemTotals()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'room_package_id',

        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RoomServiceSoldItem::with([
            'room', 'department',
            'room_package', 'stock', 'unit', 'guest', 'user', 'cashier', 'accompaniment'
        ]);

        $main_query->when(count($filled) > 0, function ($query) use ($filled) {

            foreach ($filled as $column => $value) {
                $query->where($column,  $value);
            }
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }

        $main_query->where(Parent::branch_array())->selectRaw('*,
        SUM(row_total) AS sum_row_total,
        SUM(row_vat) AS sum_row_vat,
        SUM(cost_total) AS total_cost,
        SUM(service_charge_amount) AS sum_service_charge_amount
       ');
        $res = $main_query->first();

        return response()->json($res);
    }


    function completeCreditSale(Request $request)
    {
        $request->validate(['customer_id' => "required", 'order_no' => 'required', 'receipt_total' => 'numeric|required|min:1']);
        DB::transaction(function () use ($request) {
            $res = RoomOrder::where(['order_no' => $request->order_no])->get();

            $customer_name = Customer::where('id', $request->customer_id)->first()->company_name;
            $insert_ledger_data = array(


                'ref' => $request->receipt_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'credit sales',
                'credit' => "Stock",
                'debit' => $customer_name,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {

                    OrderCart::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $request->customer_id,
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'branch_id' => $value['branch_id'],

                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'selling_price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'tax_amount' => $value['row_vat'],
                            'order_no' => $value['order_no'],

                        ]

                    );
                    Sale::create([
                        "stock_id" => $value['stock_id'],
                        'customer_id' => $request->customer_id,
                        'branch_id' => $value['branch_id'],
                        'user_id' => $value['user_id'],
                        'order_date' => $value['order_date'],
                        'order_no' => $value['order_no'],
                        'qty' => $value['qty'],
                        'item_price' => $value['price'],
                        'row_total' => $value['row_total'],
                        'order_total' => $request->receipt_total,
                        'tax_amount' => $value['row_vat'],

                    ] + Parent::user_branch_id());
                    RoomServiceSoldItem::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'department_id' => $value['department_id'],
                            'user_id' => $value['user_id'],
                            'branch_id' => $value['branch_id'],
                            'cashier_id' => Parent::user_id(),
                            'room_reservation_id' => $value['room_reservation_id'],
                            'room_id' => $value['room_id'],
                            'guest_id' => $value['guest_id'],
                            'room_package_id' => $value['room_package_id'],
                            'no_guest' => $value['no_guest'],
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],
                            'prev_order_no' => $value['prev_order_no'],
                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'service_charge_amount' => $value['service_charge_amount'],

                            'service_charge_rate' => $value['service_charge_rate'],
                        ]

                    );


                    AllSaleDetailsReport::create(
                        [
                            'stock_id' => $value['stock_id'],
                            "product_category_id" => Parent::product_category_id($value['stock_id']),
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'location_id' => $value['location_id'],
                            'department_id' => $value['department_id'],
                            'table_id' => $value['table_id'],
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],

                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'accompaniment_id' => $value['accompaniment_id']
                        ]
                            + Parent::branch_array()

                    );
                }
            }


            RoomOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }

    function fetchPendingWaiterSales()
    {
        $main_query =   RoomServiceSale::with(['customer', 'waiter', 'guest'])->where(["cashier_status" => "uncleared"]);
        $main_query->when(request('search') != '', function ($query) {
            $query->where("order_no", 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('waiter', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('waiter_id', Parent::user_id());
        }
        $res = $main_query->get();
        return response()->json($res);
    }

    function clearWaiterSales(Request $request)
    {
        $order_no = $request->order_no;
        $res = RoomServiceSale::where([
            'cashier_status' => "uncleared",
            "order_no" => $order_no
        ])->update(['cashier_status' => "cleared", 'cashier_id' => Parent::user_id()]);
        return true;
    }

    function fetchGroupedCategory()
    {



        $waiter_id = request('waiter_id');
        $cashier_id = request('cashier_id');


        $main_query = AllSaleDetailsReport::with(["stock", "product_category"]);
        if (request('from') != "" && request('to') != "") {
            // $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
            // $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->when($waiter_id != '', function ($query) use ($waiter_id) {

            $query->where('user_id',  $waiter_id);
        });
        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {

            $query->where('cashier_id',  $cashier_id);
        });
        $main_query->selectRaw("*,SUM(row_total) AS sum_row_total")->groupBy('product_category_id');
        $res1 = $main_query->get();
        $category_array = array();

        foreach ($res1 as $value) {

            $raw_query =  AllSaleDetailsReport::with(['stock'])
                ->whereHas('stock', function ($query) use ($value) {
                    $query->where('product_category_id', $value['product_category_id']);
                });
                $raw_query->when($waiter_id != '', function ($query) use ($waiter_id) {

                    $query->where('user_id',  $waiter_id);
                });
                $raw_query->when($cashier_id != '', function ($query) use ($cashier_id) {
        
                    $query->where('cashier_id',  $cashier_id);
                });
            if (request('from') != "" && request('to') != "") {
                //  $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
                //$to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
                $from = request('from');
                $to = request('to');
                $raw_query->whereBetween('created_at', [$from, $to]);
            }
            $raw_query->selectRaw("stock_id,price,
            SUM(row_total) AS sum_row_total,
             SUM(qty) AS sum_qty")
                ->groupBy(['stock_id']);
            $res3 = $raw_query->get()->toArray();

            $category_array[] = array(
                "category_id" => $value['product_category_id'],
                "category_name" => $value['product_category']->name,
                'category_total' => $value['sum_row_total'],
                "data" => $res3
            );
        }
        return response()->json($category_array);
    }

    function groupedDeptTotals()
    {
        $main_query = AllSaleDetailsReport::with(["stock", "department"]);

        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->selectRaw("*,SUM(row_total) AS sum_row_total")->groupBy('department_id');
        $res1 = $main_query->get();



        return response()->json($res1);
    }
}
