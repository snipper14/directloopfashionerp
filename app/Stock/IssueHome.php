<?php

namespace App\Stock;

use App\User;
use Illuminate\Database\Eloquent\Model;

class IssueHome extends Model
{
    protected $fillable=[
        'user_id',
        'stock_id',
        'qty', 
        'date_issued',
        'purchase_price',
        'row_total',
        'unit_id'

    ];

    public function user(){
         return $this->belongsTo(User::class)->withDefault(["user"=>'']);  
    }

    public function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock'=>'']);
    }
}
