<?php

namespace App\HouseKeeping;

use App\Unit\Unit;
use App\Rooms\Room;
use App\Stock\Stock;
use App\Employee\Employee;
use App\Rooms\RoomPackage;
use App\Reservation\RoomReservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseKeepingConsumable extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'room_id',
        'room_package_id',
        'house_keeper_id',
        'room_reservation_id',
        'stock_id',
        'unit_id',
        'qty',
        'price',
        'total',
        'details',
    ];


    public function room(){
        return $this->belongsTo(Room::class)->withTrashed();
    }
    public function room_package(){
        return $this->belongsTo(RoomPackage::class)->withTrashed();
    }
    public function house_keeper(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function room_reservation(){
        return $this->belongsTo(RoomReservation::class)->withTrashed();
    }

    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function unit(){
        return $this->belongsTo(Unit::class)->withTrashed();
    }
}
