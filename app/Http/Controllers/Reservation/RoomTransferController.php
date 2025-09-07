<?php

namespace App\Http\Controllers\Reservation;

use Carbon\Carbon;
use App\Rooms\Room;
use App\RoomTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Reservation\RoomReservation;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Reservation\RoomTransferRequest;

class RoomTransferController extends BaseController
{
    function update(RoomTransferRequest $request)
    {
        DB::transaction(function () use ($request) {
            $id = $request->id;
            $reservations = RoomReservation::where('id', $id)->get()->toArray();
            foreach ($reservations as $data) {
                print_r($data['room_id']);
                Room::where('id', $data['room_id'])->update(['occupation' => "empty", 'usage_status' => "not_ready"]);

                RoomTransfer::create(Parent::user_branch_id() + ["room_reservation_id" => $id] + $data);
            }

            RoomReservation::where('id', $id)->update($request->validated());
            Room::where('id', $request->room_id)->update(['occupation' => "accupied"]);
        }, 5);
        return true;
    }

    function fetch()
    {
        // $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
        // $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();

        $main_query = RoomTransfer::with(['room_reservation.room', 'room_reservation.room_package', 'room', 'room_package', 'user']);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {
            $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();

            $query->whereBetween('updated_at', [$from, $to]);
        });

        $main_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('guest', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });
        $main_query->orderBy('id', 'DESC');
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }
}
