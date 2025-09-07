<?php

namespace App\Reception;

use App\Scopes\BranchScope;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceptionBalance extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'opening_amount', 
        'closing_amount', 
        'sold_amount',
        'total_amount', 
        'cash_amount', 
        'bank_transfer_amount', 
        'mobile_money', 
        'online_paid', 
        'credit_card', 
        'cheque', 
        "opening_time",
        "closing_time",
        'pending_closing',
        'deficit_amount',
       'details',
       'cash_sold_amount',
       'mobile_money_sold_amount',
       'online_sold_amount',
       'card_sold_amount',
       'bank_transfer_sold_amount' ,
       'cheque_sold_amount',
    ];
function user(){
    return $this->belongsTo(User::class)->withDefault(['user'=>""]);
}
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
