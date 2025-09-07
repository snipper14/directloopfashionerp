<?php

namespace App\Http\Controllers\HouseKeeping;

use App\HouseKeeping\HouseKeepingConsumable;
use App\Rooms\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reservation\RoomReservation;
use App\Http\Controllers\BaseController;
use App\Http\Requests\HouseKeeping\HouseKeepingConsumablesRequest;
use phpDocumentor\Reflection\Types\Parent_;

class HouseKeepingConsumablesController extends BaseController
{

    function create(HouseKeepingConsumablesRequest $r)
    {
        $res =  RoomReservation::where('room_id', $r->room_id)->latest('created_at')->first();

        $room_reservation_id = $res ? $res->id : '';
        $room_package_id = $res ? $res->room_package_id : '';

        HouseKeepingConsumable::create($r->validated() +
            [
                'room_reservation_id' => $room_reservation_id,
                'room_package_id' => $room_package_id,
                'unit_id' => Parent::getUnitId($r->stock_id)

            ] + Parent::user_branch_id());
        $res =  HouseKeepingConsumable::with([
            'room', 'room_package',
            'house_keeper', 'room_reservation', 'stock', 'unit'
        ])->where(['room_reservation_id' => $room_reservation_id,])->get();
        return response()->json($res);
    }

    function pendingHseConsumableDetails()
    {
        $res =  RoomReservation::where(['room_id' => request('room_id'), 'house_keeping_status' => 'pending'])->latest('created_at')->first();

        $room_reservation_id = $res ? $res->id : '';
        $res =  HouseKeepingConsumable::with([
            'room',
            'room_reservation',
            'room_package',
            'house_keeper', 'stock', 'unit'
        ])->where(['room_reservation_id' => $room_reservation_id,])->get();
        return response()->json($res);
    }


    public function completeHouseKeeping(Request $request)
    {
        if ($request->room_reservation_id) {
            $res =  RoomReservation::where('room_id', $request->room_id)->first();

            $room_id = $res ? $res->room_id : '';
            RoomReservation::where(['id' => $request->room_reservation_id])->update(['house_keeping_status' => 'cleared']);
            Room::where('id', $room_id)->update(['usage_status' => 'ready']);
            return true;
        } else {
            $res =  RoomReservation::where('room_id', $request->room_id)->latest('created_at')->first();
         if($res){  RoomReservation::where(['id' => $res->room_reservation_id])
            ->update(['house_keeping_status' => 'cleared']);}
            Room::where('id', $request->room_id)->update(['usage_status' => 'ready']);
            return true;
            $room_reservation_id = $res ? $res->id : '';
        }
    }
    function fetch()
    {
        $raw_query = HouseKeepingConsumable::with([
            'room',
            'room_reservation',
            'room_package',
            'house_keeper', 'stock', 'unit'
        ])
       -> when(request('search', '') != '', function ($query) {
           $query->whereHas('room',function($q){
             $q->Where('door_name', 'LIKE', '%' . request('search') . '%')
               ;
               })
               ->orWhereHas('room_package',function($q){
                $q->Where('name', 'LIKE', '%' . request('search') . '%')
                  ;
                  })
                  ->orWhereHas('house_keeper',function($q){
                    $q->Where('first_name', 'LIKE', '%' . request('search') . '%')->
                    orWhere('last_name', 'LIKE', '%' . request('search') . '%')
                      ;
                      });
         
        })
        ->selectRaw('*,SUM(total) AS total_consumable,SUM(qty) AS total_qty')->groupBy(['room_reservation_id', 'room_id'])->latest('created_at');
        $res= $raw_query->get();
             if(request('page')>0){
                $res= $raw_query->paginate(15);
             }
        return response()->json($res);
    }
    function fetchConsumableDetails()
    {
        $res = HouseKeepingConsumable::where('room_reservation_id', request('room_reservation_id'))->with(['room', 'stock', 'unit'])
            ->latest('created_at')->get();
        return response()->json($res);
    }
    function  fetchReservations()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'guest_id',
            'clear_status'
        ]));
        $raw_query = RoomReservation::with(['house_keeper', 'waiter', 'guest', 'room.room_standard', 'room_package']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });

        $res =   $raw_query->where(Parent::branch_array())->get();
        if (request('page') > 0) {
            $res = $raw_query->where(Parent::branch_array())->paginate(20);
        }
        return response()->json($res);
    }

    function destroy(Request $request)
    {
        $id = $request->route('id');

        $res = HouseKeepingConsumable::where('id', $id)->delete();
        return true;
    }
}
