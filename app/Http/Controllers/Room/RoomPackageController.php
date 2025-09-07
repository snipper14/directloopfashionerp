<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomPackageRequest;
use App\Rooms\RoomPackage;
use Illuminate\Http\Request;

class RoomPackageController extends BaseController
{
    public function create(RoomPackageRequest $request){
        RoomPackage::create($request->validated()+Parent::user_branch_id());
        $res=RoomPackage::where(Parent::branch_array())->get();
        return response()->json($res);
    }
    public function fetch(){
        return response()->json(RoomPackage::where(Parent::branch_array())->get());

    }

    public function update(RoomPackageRequest $request){
        RoomPackage::where('id',$request->id)->update($request->validated());
        $res=RoomPackage::where(Parent::branch_array())->get();
        return response()->json($res);
    }

    public function destroy(Request $request){
        RoomPackage::where('id',$request->route('id'))->delete();
        return true;
    }
}
