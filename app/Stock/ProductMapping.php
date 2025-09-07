<?php

namespace App\stock;

use App\dept\Department;
use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMapping extends Model
{
    use SoftDeletes;
    protected $fillable=[
       'user_id',
       'stock_id',
       'element_id',
       'department_id',
        'qty',
       'branch_id',
    ];

    public function department(){
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department'=>'']);
    }
    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock'=>'']);
    }
    public function element(){
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['element'=>'']);
    }
}
