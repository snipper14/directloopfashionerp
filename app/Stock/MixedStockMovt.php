<?php

namespace App\Stock;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MixedStockMovt extends Model
{
    use SoftDeletes;
    protected $fillable=[
        
       'branch_id',
       'user_id',
       'stock_id',
       'opening_stock',
     
       'grn_qty',
        'sold_items',
        'issued_qty',
        'system_qty',
       'physical_qty',
       "date_recorded"
    ];

    function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>'']);
    }

    function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock'=>'']);
    }
}
