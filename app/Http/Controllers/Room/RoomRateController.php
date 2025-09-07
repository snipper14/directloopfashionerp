<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomRateRequest;
use App\Rooms\RoomRate;
use Illuminate\Http\Request;

class RoomRateController extends BaseController
{
    public function create(RoomRateRequest $request)
    {
        RoomRate::create($request->validated() + Parent::user_branch_id());
        $res = RoomRate::with(['room_standard', 'room_package'])->where(Parent::branch_array())->get();
        return response()->json($res);
    }
    public function fetch()
    {

        $raw_query = RoomRate::when(request('occupation', '') != '', function ($query) {
            $query->whereHas('room', function ($query) {
                $query->where('occupation',  request('occupation'));
            });
        })
            ->when(request('usage_status', '') != '', function ($query) {
                $query->whereHas('room', function ($query) {
                    $query->where('usage_status', request('usage_status'));
                });
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })->orWhereHas('room_standard', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                });
            })->with(['room_standard', 'room_package']);

        $raw_query->orderBy('id', 'DESC')->where(Parent::branch_array());
        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(10);
        }
        return response()->json($data);
    }

    public function update(RoomRateRequest $request)
    {
        RoomRate::where('id', $request->id)->update($request->validated());
        $res = RoomRate::with(['room.room_standard', 'room_standard', 'room_package'])->where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        RoomRate::where('id', $request->route('id'))->delete();
        return true;
    }

    function checkRate(Request $request)
    {
        $res = RoomRate::with(["room_standard", "room_package"])->where(["room_standard_id" => $request->room_standard_id])->get();
        return response()->json($res);
    }
}
