<?php

namespace App\POS;

use App\Guest\Guest;
use App\Reservation\RoomReservation;
use App\dept\Department;
use App\Rooms\Room;
use App\Rooms\RoomPackage;
use App\Stock\Stock;
use App\Unit\Unit;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'guest_id',
        'room_id',

        'room_package_id',
        'stock_id',
        'unit_id',
        'room_reservation_id',
        'department_id',
        'qty',
        'price',
        'row_total',
        'row_vat',
        'vat_rate',
        'no_guest',
        'notes',
        'order_date',
        'order_no',
        'prev_order_no',
        'merged_order_no',
        'isComplete', //['1','0'])->default('0');
        'order_type',
        'cost_price',
        'service_charge_amount',
        'service_charge_rate',
        'cost_total',
        'sub_total',
        'orderPrinted',//['1','0']
        'accompaniment_id',
        'isBillPrinted'//[1,0]
        
    ];

    public function accompaniment(){
        return $this->belongsTo(Stock::class)->withDefault([
            'accompaniment' => "",

        ]);;
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault(['department' => "department"]);
    }
    public function guest()
    {
        return $this->belongsTo(Guest::class)->withTrashed();
    }
    public function room()
    {
        return $this->belongsTo(Room::class)->withTrashed();
    }
    public function room_package()
    {
        return $this->belongsTo(RoomPackage::class)->withTrashed();
    }

    public function room_reservation()
    {
        return $this->belongsTo(RoomReservation::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }
}
