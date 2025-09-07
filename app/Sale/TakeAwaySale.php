<?php

namespace App\Sale;

use App\User;
use App\Customer\Customer;
use App\Sale\TakeAwaySoldItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TakeAwaySale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_no',
        'receipt_no',
        'customer_id',
        'user_id',
        'cashier_id',
        'branch_id',
        'order_date',
        'paid_date',
        'qty',
        'receipt_total',
        'receipt_balance',
        'total_vat',
        'cash_pay',
        'mpesa_pay',
        'card_pay',
        'credit_pay',
        'order_type',
        'payment_ref',

    ];

    public function take_away_sold_items()
    {
        return $this->hasMany(TakeAwaySoldItem::class);
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
}
