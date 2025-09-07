<?php

namespace App\Requistion;

use App\dept\Department;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requistion extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'department_id',
        'stock_id',
        'branch_id',
        'qty',
        'qty_issued',
        'date_due',
         'priority',
        'product_type',
        'details',
        'req_id',
        'unit_price',
        'status',//,['progress','complete']
    ];

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function department(){
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed();
    }
}
