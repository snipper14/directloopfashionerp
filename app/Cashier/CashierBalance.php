<?php

namespace App\Cashier;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CashierBalance extends Model
{

    protected $fillable = [
        'branch_id',
        'user_id',

        'closing_amount',
        'sold_amount',

        "opening_time",
        "closing_time",
        'details',
        'pending_closing', // ['0', '1']

        'mobile_money_collected',
        'card_collected',

        'cash_sold_amount',
        'mobile_money_sold_amount',
        'card_sold_amount',
        'conference_sales',
        'cash_left_amount',
        'closing_cash_amount',
        'cash_collected',
        'opening_amount',
        'invoice_payments', 
        'cash_sales_amount', 
        'expense_payments'

    ];
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
