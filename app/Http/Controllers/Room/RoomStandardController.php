<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomStandardRequest;
use App\Rooms\RoomStandard;

class RoomStandardController extends BaseController
{
    public function create(RoomStandardRequest $request){
        RoomStandard::create($request->validated()+Parent::user_branch_id());
        $res=RoomStandard::where(Parent::branch_array())->get();
        return response()->json($res);
    }
    public function fetch(){
        return response()->json(RoomStandard::where(Parent::branch_array())->get());

    }

    public function update(RoomStandardRequest $request){
        RoomStandard::where('id',$request->id)->update($request->validated());
        $res=RoomStandard::where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request){
        RoomStandard::where('id',$request->route('id'))->delete();
        return true;
    }
}
