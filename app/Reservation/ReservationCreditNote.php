<?php

namespace App\Reservation;

use App\Scopes\BranchScope;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationCreditNote extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'credit_no',
        'details',
        'room_reservation_id',
       "qty",
       'price',
       "total",
    ];


    public function room_reservation(){
        return $this->belongsTo(RoomReservation::class)->withDefault(["room_reservation"=>""]);
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
