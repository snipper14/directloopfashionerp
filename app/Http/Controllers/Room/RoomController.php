<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomRequest;
use App\Rooms\Room;
use Illuminate\Http\Request;

class RoomController extends BaseController
{
    public function create(RoomRequest $request)
    {
        Room::create($request->validated() + Parent::user_branch_id());
        $res = Room::with('room_standard')->where(Parent::branch_array())->get();
        return response()->json($res);
    }
    public function fetch()
    {
        $raw_query = Room::with(['room_standard']);
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where('door_name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('floor_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas("room_standard", function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        $raw_query->when(request('occupation', '') != '', function ($query) {
            $query->where('occupation', 'LIKE', '%' . request('occupation') . '%');
        });
        $raw_query->when(request('usage_status', '') != '', function ($query) {
            $query->where('usage_status',  request('usage_status'));
        });
        $res = $raw_query->where(Parent::branch_array())->orderBy('door_name', "ASC")->get();
        return response()->json($res);
    }


    public function update(RoomRequest $request)
    {
        Room::where('id', $request->id)->update($request->validated());
        $res = Room::with('room_standard')->where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request)
    {
        Room::where('id', $request->route('id'))->delete();
        return true;
    }

    public function updateMaintenanceStatus(Request $request)
    {
        $request->validate([
            'is_under_maintenance' => 'required',
            'room_id' => 'required'
        ]);
        Room::where('id', $request->room_id)->update(['is_under_maintenance' => $request->is_under_maintenance]);
        return true;
    }
}
