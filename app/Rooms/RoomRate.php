<?php

namespace App\Rooms;

use App\Rooms\Room;
use App\Rooms\RoomPackage;
use App\Rooms\RoomStandard;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomRate extends Model
{
    use SoftDeletes;
    protected $fillable=[
       'branch_id',
       'user_id',
       'room_id',
       "room_standard_id",
       'room_package_id',
       'rate', 
       'meal_rate',
       'room_rate',
       
      
       
    ];

    public function room(){
        return $this->belongsTo(Room::class)->withDefault(["room"=>""]);
    }

    public function room_package(){
        return $this->belongsTo(RoomPackage::class)->withDefault(["room_package"]);
    }
    public function room_standard(){
        return $this->belongsTo(RoomStandard::class)->withDefault(["room_standard"=>""]);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
