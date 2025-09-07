<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Rooms\Room;
use App\Guest\Guest;
use App\Stock\Stock;
use App\dept\Department;
use App\Rooms\RoomPackage;
use App\Scopes\BranchScope;
use App\Sale\RoomServiceSale;
use App\Reservation\RoomReservation;
use Illuminate\Database\Eloquent\Model;

class RoomServiceSoldItem extends Model
{
    protected $fillable = [
        'order_no',
        'stock_id',
        'unit_id',
        'guest_id',
        'user_id',
        'cashier_id',
        'branch_id',
        'room_id',
        'room_reservation_id',
        'room_package_id',
        'room_service_sale_id',
        'department_id',
        'order_date',
        'cost_price',
        'cost_total',
        'qty',
        'price',
        'row_total',
        'vat_rate',
        'row_vat',
        'no_guest',
        'service_charge_amount',
        'service_charge_rate',
        'order_type',

        'prev_order_no',
        'accompaniment_id'
    ];

    public function accompaniment(){
        return $this->belongsTo(Stock::class)->withDefault([
            'accompaniment' => "",

        ]);;
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            "department" => ""
        ]);
    }
    public function room_service_sale()
    {
        return $this->belongsTo(RoomServiceSale::class);
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
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
