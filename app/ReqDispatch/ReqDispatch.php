<?php

namespace App\ReqDispatch;

use App\Branch\Branch;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ReqDispatch extends Model
{
protected $fillable=[
    'stock_id',
    'user_id',
    'issuer_id',
    'branch_id',
    "details",
    'issue_date',
    "dispatch_qty",
    "order_qty",
    "pending_qty",
    "req_id"
];

public function stock(){
    return $this->belongsTo(Stock::class)->withTrashed();
}
public function issuer(){
    return $this->belongsTo(User::class)->withTrashed();
}

public function user(){
    return $this->belongsTo(User::class)->withTrashed();
}
public function branch(){
    return $this->belongsTo(Branch::class)->withTrashed();
}

}
