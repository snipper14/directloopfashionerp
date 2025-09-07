<?php

namespace App\Http\Controllers\RoomOrder;

use Carbon\Carbon;
use App\Stock\Stock;
use App\POS\RoomOrder;
use Illuminate\Http\Request;
use App\Rules\OrderAvailability;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\RoomOrder\RoomOrderRequest;
use App\Rules\ValidateCrementRoomServiceOrderPrintStatus;
use App\Rules\ValidateDeleteRoomServiceOrderPrinted;

class RoomOrderController extends BaseController
{
    public function create(RoomOrderRequest $request)
    {
        if ($request->isBar) {
            $this->validate($request, ['qty' => new OrderAvailability($request->stock_id)]);
        }
        DB::transaction(function () use ($request) {


            RoomOrder::create($request->validated() + Parent::user_branch_id());


            Stock::where(['id' => $request->stock_id])->decrement('qty', $request->qty);
        }, 5);
        $res = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where(['order_no' => $request->order_no, 'isComplete' => '0'] + Parent::branch_array())->orderBy('id', 'desc')->get();

        return response()->json($res);
    }
    public function orderPrinted($request)
    {
        $res = RoomOrder::where([
            'stock_id' => $request->stock_id,
            'order_no' => $request->order_no,
            "orderPrinted" => 0,

        ] + Parent::branch_array())->first();

        return  $res ? true : false;
    }
    public function isItemInCart(Request $request)
    {
        $res = RoomOrder::where([
            'stock_id' => $request->stock_id,
            'order_no' => $request->order_no,
        ] + Parent::branch_array())->first();

        return  $res ? true : false;
    }

    public function crementOrder(Request $request)
    {
        $this->validate($request, ['stock_id' => new ValidateCrementRoomServiceOrderPrintStatus($request->id)]);
        if ($request->update_type == 'add') {

            if ($request->isBar) {
                $this->validate($request, ['qty' => new OrderAvailability($request->stock_id)]);
            }
            RoomOrder::where(['id' => $request->id])
                ->update(array(
                    'qty' => DB::raw('qty + ' . $request->qty),
                    'row_total' => DB::raw('row_total + ' . $request->row_total),
                    'sub_total' => DB::raw('sub_total + ' . $request->sub_total),
                    'row_vat' => DB::raw('row_vat + ' . $request->row_vat),
                    'service_charge_amount' => DB::raw('service_charge_amount + ' . $request->service_charge_amount),
                    'cost_total' => DB::raw('cost_total + ' . $request->cost_total),
                ));
            Stock::where(['id' => $request->stock_id])->decrement('qty', $request->qty);
        } else if ($request->update_type == 'minus') {
            RoomOrder::where(['id' => $request->id])
                ->update(array(
                    'qty' => DB::raw('qty - ' . $request->qty),
                    'row_total' => DB::raw('row_total - ' . $request->row_total),
                    'row_vat' => DB::raw('row_vat - ' . $request->row_vat),
                    'sub_total' => DB::raw('sub_total - ' . $request->sub_total),
                    'service_charge_amount' => DB::raw('service_charge_amount - ' . $request->service_charge_amount),
                    'cost_total' => DB::raw('cost_total - ' . $request->cost_total),
                ));
            Stock::where(['id' => $request->stock_id])->increment('qty', $request->qty);
        }

        $res = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where(['order_no' => $request->order_no, 'isComplete' => '0'] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return response()->json($res);
    }
    function updateServiceCharge(Request $request)
    {
        $this->validate($request, [
            'order_no' => 'required',
            'service_charge_amount' => 'required|numeric|min:1',
            'service_charge_rate' => 'required|numeric|min:1',

        ]);
        RoomOrder::where('order_no', $request->order_no)->update([
            "service_charge_amount" => $request->service_charge_amount,
            "service_charge_rate" => $request->service_charge_rate
        ]);
        $res = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where(['order_no' => $request->order_no] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return response()->json($res);
    }


    public function getPendingOrders(Request $request)
    {
        $order_no = $request->order_no;
        $raw_query = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where([
            'isComplete' => '0',
            'room_id' => $request->room_id,

        ] + Parent::branch_array())->orderBy('id', 'desc');
        if ($order_no != '') {
            $raw_query->where('order_no', $order_no);
            $res = $raw_query->get();
            return response()->json(['status' => "orders", 'results' => $res]);
        } else {
            return response()->json(['status' => "order_no", 'results' => $this->orderNumber()]);
        }
    }


    function orderNumber()
    {


        $latest = RoomOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . '' . sprintf('%04d', $string);
        $isUnique = RoomOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . '' . ($latest->id + 1);
        }
        return  $orderNo . Parent::user_id();
    }
    function updateNoGuest(Request $request)
    {

        RoomOrder::where('id', $request->id)->update(['no_guest' => $request->no_guest]);
        $res = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where(['order_no' => $request->order_no] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return response()->json($res);
    }

    function addNote(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'notes' => 'required',
            'order_no' => 'required',
        ],  [
            'id.required' => 'You have to choose cart  item first!',

        ]);
        RoomOrder::where('id', $request->id)->update(['notes' => $request->notes]);
        $res = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where(['order_no' => $request->order_no] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return response()->json($res);
    }

    public function completeOrder(Request $request)
    {
        $order_no = $request->order_no;
        RoomOrder::where(['order_no' => $order_no])->update(['isComplete' => '1', 'orderPrinted' => '1']);
        return true;
    }

    public function destroy(Request $request)
    {
        $this->validate($request, ['id' => new ValidateDeleteRoomServiceOrderPrinted()]);
        DB::transaction(function () use ($request) {
            $id = $request->id;
            $del_res = RoomOrder::where('id', $id)->get();
            foreach ($del_res as $value) {
                Stock::where('id', $value['stock_id'])->increment('qty', $value['qty']);
            }
            RoomOrder::where('id', $id)->forceDelete();
        }, 5);
        return true;
    }
    public function destroyOrder()
    {
        DB::transaction(function () {
            $order_no = request('order_no');
            $del_res = RoomOrder::where('order_no', $order_no)->get();
            foreach ($del_res as $value) {
                Stock::where('id', $value['stock_id'])->increment('qty', $value['qty']);
            }
            RoomOrder::where('order_no', $order_no)->forceDelete();
        }, 5);
        return true;
    }

    public function validateBill(Request $request)
    {
        $res = RoomOrder::where('order_no', $request->order_no)->first()->isComplete;
        return response()->json($res);
    }

    public function fetchBills(Request $request)
    {

        $raw_query = RoomOrder::with(['room', 'guest', 'user', 'accompaniment'])
            ->when(request('search', '') != '', function ($q) {

                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('room', function ($q) {
                        $q->where('door_name', 'LIKE', '%' .  request('search') . '%');
                    })->orWhereHas('guest', function ($q) {
                        $q->where('name', 'LIKE', '%' .  request('search') . '%');
                    });
            });




        $raw_query->when(request('door_name', '') != '', function ($query) {
            $query->whereHas('room', function ($q) {
                $q->where('door_name', 'LIKE', '%' .  request('door_name') . '%');
            });
        });
        $raw_query->when(request('room_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('room_id', request('room_id'));
            });
        });
        $raw_query->where(
            Parent::branch_array() + [

                // 'isComplete' => '1'
            ]
        );
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }
        if (request('bill_printed_status')) {
            $raw_query->where("isBillPrinted", "0");
        }
        $raw_query->selectRaw('*,SUM(row_total) as order_total,SUM(row_vat) AS total_tax,
        SUM(sub_total) AS sum_sub_total,SUM(service_charge_amount) AS sum_service_charge_amount
        ')

            ->groupBy(['order_no'])->orderBy('id', 'desc');

        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $order_array = array();
            foreach ($res as $value) {
                $data = RoomOrder::with([
                    'department',
                    'guest',
                    'room',
                    'room_package',
                    'room_reservation',
                    'user',
                    'unit',
                    'stock.tax_dept', 'accompaniment'
                ])->where('order_no', $value['order_no'])->get();
                $order_array[] = array(
                    'order_no' => $value['order_no'],
                    'order_total' => $value['order_total'],
                    'no_guest' => $value['no_guest'],
                    'isComplete' => $value['isComplete'],
                    'total_tax' => $value['total_tax'],
                    'sum_sub_total' => $value['sum_sub_total'],
                    'created_at' => $value['created_at'],
                    "isBillPrinted" => $value['isBillPrinted'],
                    'sum_service_charge_amount' => $value['sum_service_charge_amount'],
                    'door_name' => $value['room']->door_name,
                    'guest_name' => $value['guest']->name,
                    'user' => $value['user'],
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
        RoomOrder::where('order_no', $order_no)->update(['isComplete' => '0']);
        return true;
    }

    function mergeBills(Request $request)
    {
        $original_order_no = $request->original_order_no;
        $merged_order_no = $request->merged_order_no;
        $original_order_details = RoomOrder::where('order_no', $original_order_no)->first();
        RoomOrder::where('order_no', $merged_order_no)->update([
            'order_no' => $original_order_no,
            'merged_order_no' => $merged_order_no,
            'isComplete' => $original_order_details->isComplete,
            'room_id' => $original_order_details->room_id,
            'guest_id' => $original_order_details->guest_id,
            'room_package_id' => $original_order_details->room_package_id,
            'room_reservation_id' => $original_order_details->room_reservation_id,
        ]);
        return true;
    }


    function fetchOrders()
    {
        $main_query = RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ]);



        $main_query->when(request('room_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('room_id', request('room_id'));
            });
        });
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->selectRaw('*,SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat,
        SUM(service_charge_amount) AS sum_service_charge_amount')
            ->groupBy('order_no')->orderBy('id', 'ASC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }

    function getByOrder()
    {
        $order_no = request('order_no');
        $res =   RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where('order_no', $order_no)->get();
        return response()->json($res);
    }
    function getByReservation()
    {
        $reservation_id = request('reservation_id');
        $res =   RoomOrder::with([
            'department',
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept', 'accompaniment'
        ])->where('room_reservation_id', $reservation_id)->get();
        return response()->json($res);
    }



    function fetchMergeBills()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = RoomOrder::withTrashed()->with([
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept'
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });


        $main_query->when(request('room_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('room_id', request('room_id'));
            });
        });
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('guest', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->where('merged_order_no', '!=', null);
        $main_query->selectRaw('*,SUM(row_total) AS sum_total,SUM(row_vat) AS sum_vat,
        SUM(service_charge_amount) AS sum_service_charge_amount')
            ->groupBy('order_no')->orderBy('id', 'ASC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }

    function fetchMergeBillDetails()
    {
        $res = RoomOrder::withTrashed()->with([
            'guest',
            'room',
            'room_package',
            'room_reservation',
            'user',
            'unit',
            'stock.tax_dept'
        ])->where(['order_no' => request('order_no'),] + Parent::branch_array())->orderBy('id', 'desc')->get();
        return response()->json($res);
    }



    function fetchAllBills()
    {
        $raw_query = RoomOrder::with(['room', 'guest', 'user', 'accompaniment'])
            ->when(request('search', '') != '', function ($q) {

                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('room', function ($q) {
                        $q->where('door_name', 'LIKE', '%' .  request('search') . '%');
                    })->orWhereHas('guest', function ($q) {
                        $q->where('name', 'LIKE', '%' .  request('search') . '%');
                    });
            });




        $raw_query->when(request('door_name', '') != '', function ($query) {
            $query->whereHas('room', function ($q) {
                $q->where('door_name', 'LIKE', '%' .  request('door_name') . '%');
            });
        });
        $raw_query->when(request('room_id', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('room_id', request('room_id'));
            });
        });
        $raw_query->where(
            Parent::branch_array() + [

                // 'isComplete' => '1'
            ]
        );
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }

        $raw_query->selectRaw('*,SUM(row_total) as order_total,SUM(row_vat) AS total_tax,
    SUM(sub_total) AS sum_sub_total,SUM(service_charge_amount) AS sum_service_charge_amount
    ')

            ->groupBy(['order_no'])->orderBy('id', 'desc');

        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $order_array = array();
            foreach ($res as $value) {
                $data = RoomOrder::with([
                    'department',
                    'guest',
                    'room',
                    'room_package',
                    'room_reservation',
                    'user',
                    'unit',
                    'stock.tax_dept', 'accompaniment'
                ])->where('order_no', $value['order_no'])->get();
                $order_array[] = array(
                    'order_no' => $value['order_no'],
                    'order_total' => $value['order_total'],

                    'no_guest' => $value['no_guest'],
                    'isComplete' => $value['isComplete'],
                    'total_tax' => $value['total_tax'],
                    'sum_sub_total' => $value['sum_sub_total'],
                    'created_at' => $value['created_at'],
                    'sum_service_charge_amount' => $value['sum_service_charge_amount'],
                    'door_name' => $value['room']->door_name,
                    'guest_name' => $value['guest']->name,
                    'user' => $value['user'],
                    "results" => $data
                );
            }

            return response()->json($order_array);
        } else {
            return response()->json($res);
        }
    }

    function updateBillPrintStatus(Request $request)
    {

        RoomOrder::where("order_no", $request->order_no)->update(["isBillPrinted" => "1"]);
        return true;
    }

    function enableBillReprint(Request $request)
    {
        RoomOrder::where("order_no", $request->order_no)->update(["isBillPrinted" => "0"]);
        return true;
    }
}
