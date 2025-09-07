<?php

namespace App\Http\Controllers\RoomMaintenance;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Room\RoomMaintenanceRequest;
use App\Room\RoomMaintenance;
use Illuminate\Http\Request;

class RoomMaintenanceController extends BaseController
{
    public function create(RoomMaintenanceRequest $request){
        RoomMaintenance::create($request->validated()+Parent::user_branch_id());

        $res=RoomMaintenance::where('room_id',$request->room_id)->orderBy('id','DESC')->paginate(10);
        return response()->json($res);
    }

    public function getMaintenanceRecords($room_id){
        $query=RoomMaintenance::where('room_id',$room_id)->orderBy('id','DESC');
        $res=$query->get();
        if(request('page')>0){
           $res=$query->paginate(10);
        }
        return $res;
    }

    public function fetch(){
      $res=$this->getMaintenanceRecords(request('room_id'));
      return response()->json($res);

    }
    public function destroy(Request $request){
        RoomMaintenance::where('id',$request->id)->delete() ;
        return true;

    }
}
