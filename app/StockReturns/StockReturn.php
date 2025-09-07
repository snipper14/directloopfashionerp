<?php

namespace App\StockReturns;

use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockReturn extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'stock_id',
        'return_user_id',
        'user_id',
        'branch_id',
        'department_id',
        "qty_returned",
        "date_returned",
        'return_code',
        'status',
        "details"
    ];


    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    public function return_user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

  
}
