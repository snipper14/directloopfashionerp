<?php

namespace App\Reservation;

use App\User;
use App\Rooms\Room;
use App\Guest\Guest;
use App\Branch\Branch;
use App\Employee\Employee;
use App\Guest\GuestCompany;
use App\Rooms\RoomPackage;
use App\Rooms\RoomRate;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomReservation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'branch_id',
        'guest_id',
        'guest_company_id',
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
        'price',
        'total',
        'qty',
        'clear_status', //['occupied','cleared']
        'house_keeping_status', //[['pending','cleared']]
        'checkout_date',
        'house_keeper_id',
        'waiter_id',
        'room_id',
        'room_rate_id',
        'pay_method',
        'ref_no',
        'room_package_id',
        'tax_amount',
        'tax_rate',
        'amount_paid',

        'cash_paid',
        'mobile_money_paid',
        'card_paid',
        'cheque_paid',
        'bank_transfer_paid',
        'credit_note_total',
        'online_paid',

    ];
   public function guest_company(){
       return $this->belongsTo(GuestCompany::class)->withTrashed()->withDefault(["guest_company"=>""]);
   }
   public function room_rate()
   {
       return $this->belongsTo(RoomRate::class)->withTrashed();
   }
    public function house_keeper()
    {
        return $this->belongsTo(Employee::class)->withTrashed()->withDefault([
            'first_name' => "",
            'last_name' => "",
        ]);
    }
    public function waiter()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault([
            'name' => '',

        ]);
    }
    public function guest()
    {
        return $this->belongsTo(Guest::class)->withTrashed()->withDefault(["guest" => ""]);
    }
    public function room()
    {
        return $this->belongsTo(Room::class)->withTrashed()->withDefault(["room" => ""]);
    }
    public function room_package()
    {
        return $this->belongsTo(RoomPackage::class)->withTrashed()->withDefault(["room_package" => ""]);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["user"=>""]);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
