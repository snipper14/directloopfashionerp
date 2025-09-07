<?php

namespace App\Assembly;

use App\Branch\Branch;
use App\dept\Department;
use App\Ingredient\Ingredient;
use App\Requistion\Requistion;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;

class AssemblyProductRequisition extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'stock_id',
        'ingredient_id',

        'branch_id',
        'qty',
        'ingredient_price',
        'ingredient_qty',
        'ingredient_qty_issued',
        'sub_total',
        'date_due',
        'approved',
        'status',
        'req_id',
        'details',
        'is_stock_updated',
        'final_qty_produced'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
   

    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function ingredient()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
}
