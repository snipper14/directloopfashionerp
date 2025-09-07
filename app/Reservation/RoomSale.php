<?php

namespace App\Reservation;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomSale extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'sold_on',
        'room_reservation_id',
       'amount_paid',
       'cash_paid',
       'mobile_money_paid',
       'online_paid',
       'card_paid',
       'cheque_paid',
       'bank_transfer_paid',
       'user_id',
       'branch_id',
       'details'
    ];

    function room_reservation(){
        return $this->belongsTo(RoomReservation::class)->withDefault(['room_reservation'=>'']);
    }
    function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
