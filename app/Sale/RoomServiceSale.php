<?php

namespace App\Sale;

use App\User;
use App\Guest\Guest;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use App\Sale\RoomServiceSoldItem;
use Illuminate\Database\Eloquent\Model;

class RoomServiceSale extends Model
{
    protected $fillable = [
        'order_no',
        'receipt_no',
        'guest_id',
        'user_id',
        'cashier_id',
        'branch_id',
        'order_date',
        'paid_date',
        'qty',

        'receipt_total',
        'total_vat',
        'service_charge_total',
        'receipt_balance',
        'cash_pay',
        'mpesa_pay',
        'card_pay',
        'credit',
        'order_type',
        'payment_ref',
        'cashier_status',//['cleared','uncleared']
        'waiter_id'
    ];
    public function guest()
    {
        return $this->belongsTo(Guest::class)->withTrashed();
    }
    public function room_service_sold_items()
    {
        return $this->hasMany(RoomServiceSoldItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function waiter(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["waiter"=>""]);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
