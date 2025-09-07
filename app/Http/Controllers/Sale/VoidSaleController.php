<?php

namespace App\Http\Controllers\Sale;

use App\User;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\POS\RoomOrder;
use App\Sale\VoidedSale;
use App\Customer\Customer;
use App\Online\OnlineOrders;
use Illuminate\Http\Request;
use App\TakeAway\TakeawayOrder;
use App\Transaction\Transaction;
use App\Rules\ValidateVoidAllQty;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateVoidSingleQty;
use App\Rules\VlidateVoidSingleOnline;
use App\Http\Controllers\BaseController;

class VoidSaleController extends BaseController
{
    public function voidSale(Request $request)
    {
        DB::transaction(function () use ($request) {
            $request->validate([
                'notes' => "nullable"
            ]);
            $res = POSOrder::where(['order_no' => $request->order_no])->get();


            $user = User::where('id', Parent::user_id())->first()->name;
            $insert_ledger_data = array(


                'ref' => $request->order_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'Voided Sales',
                'credit' => "Stock",
                'debit' => $user,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    VoidedSale::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'location_id' => $value['location_id'],
                            'table_id' => $value['table_id'],
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],
                            'notes' => $request->notes,
                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],
                            'accompaniment_id' => $value['accompaniment_id']
                        ]
                            + Parent::branch_array()
                    );
                }
            }


            POSOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }
    public function voidOnlineSale(Request $request)
    {
        DB::transaction(function () use ($request) {
            $request->validate([
                'notes' => "nullable"
            ]);
            $res = OnlineOrders::where(['order_no' => $request->order_no])->get();


         

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    $stock_data = Stock::with(['tax_dept'])->where('id',$value['stock_id'])->first();
                    $tax_formulae = $stock_data->tax_dept->formulae;
                    $tax_amount = $value['row_total'] - ($value['row_total'] * $tax_formulae);
                    VoidedSale::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => 1,
                            'user_id' =>  Parent::user_id(),
                            'cashier_id' => Parent::user_id(),
                            'location_id' =>1,
                            'table_id' => 1,
                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['selling_price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $tax_amount,
                            'order_type' => $value['order_type'],
                            'notes' => $request->notes,
                            'order_no' => $value['order_no'],
                            'cost_price' => $stock_data->cost_price,
                        'cost_total' => ($stock_data->cost_price * $request->qty),
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

    function voidSingle(Request $request)
    {

        $request->validate([
            'qty' => "required|numeric|min:1",
            'notes' => "nullable",
            'id' => "required"
        ]);
        $this->validate($request, ["qty" => new ValidateVoidSingleQty($request->id)]);
        DB::transaction(function () use ($request) {
            $res = POSOrder::where('id', $request->id)->first();
            if ($res) {

                VoidedSale::create(
                    [
                        'stock_id' => $res->stock_id,
                        'unit_id' => $res->unit_id,
                        'customer_id' => $res->customer_id,
                        'user_id' => $res->user_id,
                        'cashier_id' => Parent::user_id(),
                        'location_id' => $res->location_id,
                        'table_id' => $res->table_id,
                        'order_date' => $res->order_date,
                        'qty' => $request->qty,
                        'price' => $res->price,
                        'row_total' => $request->row_total,
                        'row_vat' => $request->row_vat,
                        'order_type' => $res->order_type,
                        'notes' => $request->notes,
                        'order_no' => $res->order_no,
                        'cost_price' => $request->cost_price,
                        'cost_total' => $request->cost_total,

                    ]
                        + Parent::branch_array()
                );
                POSOrder::where('id', $res->id)->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' . $request->row_total),
                    'cost_total' => DB::raw('cost_total - ' . $request->cost_total),
                    'row_vat' => DB::raw('row_vat - ' . $request->row_vat)
                ));

                $user = User::where('id', Parent::user_id())->first()->name;
                $insert_ledger_data = array(


                    'ref' => $res->order_no,
                    'entry_date' => Parent::currentDate(),
                    'description' => 'Voided Sales',
                    'credit' => "Stock",
                    'debit' => $user,
                    'amount' => $request->row_total,
                );
                Transaction::create($insert_ledger_data + Parent::user_branch_id());

                $is_order_qty_zero = POSOrder::where(['id' => $res->id])->where('qty', '<', 1)->get();
                if (!$is_order_qty_zero->isEmpty()) {
                    POSOrder::where('id', $res->id)->delete();
                }
            }
        }, 5);
    }

    function voidOnline(Request $request)
    {

        $request->validate([
            'qty' => "required|numeric|min:1",
            'notes' => "nullable",
            'id' => "required"
        ]);
        $this->validate($request, ["qty" => new VlidateVoidSingleOnline($request->id)]);
        DB::transaction(function () use ($request) {
            $res = OnlineOrders::where('id', $request->id)->first();
            if ($res) {
                $stock_data = Stock::with(['tax_dept'])->where('id',$res->stock_id)->first();
                $tax_formulae = $stock_data->tax_dept->formulae;
                $tax_amount = $res->row_total - ($res->row_total * $tax_formulae);
                VoidedSale::create(
                    [
                        'stock_id' => $res->stock_id,
                        'unit_id' => $res->unit_id,
                        'customer_id' =>1,
                        'user_id' =>  Parent::user_id(),
                        'cashier_id' => Parent::user_id(),
                        'location_id' => 1,
                        'table_id' => 1,
                        'order_date' => $res->order_date,
                        'qty' => $request->qty,
                        'price' => $res->selling_price,
                        'row_total' => $res->row_total,
                        'row_vat' =>  $tax_amount,
                        'order_type' => 'online',
                        'notes' => $request->notes,
                        'order_no' => $res->order_no,
                        'cost_price' => $stock_data->cost_price,
                        'cost_total' => ($stock_data->cost_price * $request->qty),

                    ]
                        + Parent::branch_array()
                );
                OnlineOrders::where('id', $res->id)->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' .  $res->row_total),
                    'cost_total' => DB::raw('cost_total - ' .  ($stock_data->cost_price * $request->qty))
                  
                ));

               

                $is_order_qty_zero = OnlineOrders::where(['id' => $res->id])->where('qty', '<', 1)->get();
                if (!$is_order_qty_zero->isEmpty()) {
                    OnlineOrders::where('id', $res->id)->delete();
                }
            }
        }, 5);
    }



    public function voidRoomServiceSale(Request $request)
    {
        DB::transaction(function () use ($request) {
            $request->validate([
                'notes' => "nullable"
            ]);
            $res = RoomOrder::where(['order_no' => $request->order_no])->get();


            $user = User::where('id', Parent::user_id())->first()->name;
            $insert_ledger_data = array(


                'ref' => $request->order_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'Voided Sales',
                'credit' => "Stock",
                'debit' => $user,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    VoidedSale::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'guest_id' => $value['guest_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),
                            'room_id' => $value['room_id'],

                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],
                            'notes' => $request->notes,
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


    function voidRoomServiceSingle(Request $request)
    {
        $request->validate([
            'qty' => "required|numeric|min:1",
            'notes' => "nullable",
            'id' => "required"
        ]);
        $this->validate($request, ["qty" => new ValidateVoidAllQty($request->id)]);
        DB::transaction(function () use ($request) {
            $res = RoomOrder::where('id', $request->id)->first();
            if ($res) {

                VoidedSale::create(
                    [
                        'stock_id' => $res->stock_id,
                        'unit_id' => $res->unit_id,
                        'guest_id' => $res->guest_id,
                        'user_id' => $res->user_id,
                        'cashier_id' => Parent::user_id(),
                        'guest_id' => $res->guest_id,
                        'room_id' => $res->room_id,
                        'order_date' => $res->order_date,
                        'qty' => $request->qty,
                        'price' => $res->price,
                        'row_total' => $request->row_total,
                        'row_vat' => $request->row_vat,
                        'order_type' => $res->order_type,
                        'notes' => $request->notes,
                        'order_no' => $res->order_no,
                        'cost_price' => $request->cost_price,
                        'cost_total' => $request->cost_total,
                        'accompaniment_id' => $request['accompaniment_id']

                    ]
                        + Parent::branch_array()
                );
                RoomOrder::where('id', $res->id)->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' . $request->row_total),
                    'cost_total' => DB::raw('cost_total - ' . $request->cost_total),
                    'row_vat' => DB::raw('row_vat - ' . $request->row_vat)
                ));

                $user = User::where('id', Parent::user_id())->first()->name;
                $insert_ledger_data = array(


                    'ref' => $res->order_no,
                    'entry_date' => Parent::currentDate(),
                    'description' => 'Voided Sales',
                    'credit' => "Stock",
                    'debit' => $user,
                    'amount' => $request->row_total,
                );
                Transaction::create($insert_ledger_data + Parent::user_branch_id());

                $is_order_qty_zero = RoomOrder::where(['id' => $res->id])->where('qty', '<', 1)->get();
                if (!$is_order_qty_zero->isEmpty()) {
                    RoomOrder::where('id', $res->id)->delete();
                }
            }
        }, 5);
    }


    function fetch()
    {
        ini_set('max_execution_time', 2400);
        $filled = array_filter(request()->only([
            'room_id',
            'room_package_id',

        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = VoidedSale::with([
            'table', 'cashier',
            'location', 'stock', 'unit', 'user', 'accompaniment'
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

    function voidTotals()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'room_package_id',

        ]));
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = VoidedSale::with([
            'table',
            'location', 'stock', 'unit', 'user', 'accompaniment'
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

        $main_query->where(Parent::branch_array())->selectRaw('
        SUM(row_total) AS sum_row_total,
        SUM(row_vat) AS sum_row_vat,
        SUM(cost_total) AS total_cost
       ');
        $res = $main_query->first();

        return response()->json($res);
    }


    public function voidTakeAway(Request $request)
    {
        DB::transaction(function () use ($request) {
            $request->validate([
                'notes' => "required"
            ]);
            $res = TakeawayOrder::where(['order_no' => $request->order_no])->get();


            $user = User::where('id', Parent::user_id())->first()->name;
            $insert_ledger_data = array(


                'ref' => $request->order_no,
                'entry_date' => Parent::currentDate(),
                'description' => 'Voided Sales',
                'credit' => "Voided",
                'debit' => $user,
                'amount' => $request->receipt_total,
            );
            Transaction::create($insert_ledger_data + Parent::user_branch_id());

            if (!$res->isEmpty()) {
                foreach ($res as $value) {
                    VoidedSale::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'unit_id' => $value['unit_id'],
                            'customer_id' => $value['customer_id'],
                            'user_id' => $value['user_id'],
                            'cashier_id' => Parent::user_id(),

                            'order_date' => $value['order_date'],
                            'qty' => $value['qty'],
                            'price' => $value['price'],
                            'row_total' => $value['row_total'],
                            'row_vat' => $value['row_vat'],
                            'order_type' => $value['order_type'],
                            'notes' => $request->notes,
                            'order_no' => $value['order_no'],
                            'cost_price' => $value['cost_price'],
                            'cost_total' => $value['cost_total'],

                        ]
                            + Parent::branch_array()
                    );
                }
            }


            TakeawayOrder::where(['order_no' => $request->order_no])->delete();
        }, 5);

        return  true;
    }

    function voidSingleTakeway(Request $request)
    {
        $request->validate([
            'qty' => "required|numeric|min:1",
            'notes' => "required|min:2",
            'id' => "required"
        ]);
        DB::transaction(function () use ($request) {
            $res = TakeawayOrder::where('id', $request->id)->first();
            if ($res) {

                VoidedSale::create(
                    [
                        'stock_id' => $res->stock_id,
                        'unit_id' => $res->unit_id,
                        'customer_id' => $res->customer_id,
                        'user_id' => $res->user_id,
                        'cashier_id' => Parent::user_id(),

                        'order_date' => $res->order_date,
                        'qty' => $request->qty,
                        'price' => $res->price,
                        'row_total' => $request->row_total,
                        'row_vat' => $request->row_vat,
                        'order_type' => $res->order_type,
                        'notes' => $request->notes,
                        'order_no' => $res->order_no,
                        'cost_price' => $request->cost_price,
                        'cost_total' => $request->cost_total,


                    ]
                        + Parent::branch_array()
                );
                TakeawayOrder::where('id', $res->id)->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' . $request->row_total),
                    'cost_total' => DB::raw('cost_total - ' . $request->cost_total),
                    'row_vat' => DB::raw('row_vat - ' . $request->row_vat)
                ));

                $user = User::where('id', Parent::user_id())->first()->name;
                $insert_ledger_data = array(


                    'ref' => $res->order_no,
                    'entry_date' => Parent::currentDate(),
                    'description' => 'Voided Sales',
                    'credit' => "Voided",
                    'debit' => $user,
                    'amount' => $request->row_total,
                );
                Transaction::create($insert_ledger_data + Parent::user_branch_id());

                $is_order_qty_zero = TakeawayOrder::where(['id' => $res->id])->where('qty', '<', 1)->get();
                if (!$is_order_qty_zero->isEmpty()) {
                    TakeawayOrder::where('id', $res->id)->delete();
                }
            }
        }, 5);
    }
}
