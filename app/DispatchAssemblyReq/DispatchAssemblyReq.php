<?php

namespace App\DispatchAssemblyReq;

use App\Branch\Branch;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchAssemblyReq extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'stock_id',
        'ingredient_id',
        'user_id',
        'issuer_id',
        'branch_id',
        "details",
       'issue_date',
        "final_qty",
        "dispatch_qty",
        "order_qty",
        "pending_qty",
        "req_id"
    ];

    function ingredient(){
        return $this->belongsTo(Stock::class);
    }
    function stock(){
        return $this->belongsTo(Stock::class);
    }

    function user(){
        return $this->belongsTo(User::class);
    }

    function issuer(){
        return $this->belongsTo(User::class);
    }
    function branch(){
        return $this->belongsTo(Branch::class);
    }
}
