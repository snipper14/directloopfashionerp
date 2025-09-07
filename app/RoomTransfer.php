<?php

namespace App;

use App\Rooms\Room;
use App\Rooms\RoomPackage;
use App\Scopes\BranchScope;
use App\Reservation\RoomReservation;
use Illuminate\Database\Eloquent\Model;

class RoomTransfer extends Model
{
    protected $fillable=[
        'user_id',
        'branch_id',
        'guest_id',
        'room_rate_id',
        'updated_by',
        'name',
        'id_no',
        'phone',
        'gender',
        'no_guest',
        'from',
        'to',
        'details',
        'paid', 
        'pay_method',
        'ref_no',
        'price', 
        'total', 
        'qty', 
        'tax_rate', 
        'tax_amount', 
        'amount_paid', 

        'cash_paid', 
        'mobile_money_paid', 
        'card_paid', 
        'cheque_paid', 
        'bank_transfer_paid', 


        'checkout_date',
        'house_keeper_id',
        'waiter_id',
        'room_id',
        'room_package_id',
        'room_reservation_id',

    ];

    function room_reservation(){
        return $this->belongsTo(RoomReservation::class)->withDefault(["room_reservation"=>""]);
    }
    function room(){
        return $this->belongsTo(Room::class)->withDefault(["room"=>""]);
    }
    function room_package(){
        return $this->belongsTo(RoomPackage::class)->withDefault(["room_package"=>""]);
    }
    function user(){
        return $this->belongsTo(User::class)->withDefault(["user"=>""]);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
