<?php

namespace App\Stock;

use App\Branch\Branch;
use App\User;
use App\Stock\Stock;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class StockMovt extends Model
{//
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'opening_stock',
        'grn_qty',
        'stock_returns',
        'opening_dept_stock',
        'physical_dept_qty',
        'dept_issued_qty',
        'is_internal_stock',//1,0 default 0
        'issued_qty',
        'system_qty',
        'dept_system_qty',
        'sold_items',
        'physical_qty',
        "date_recorded",
        'is_physical_update',//1,0 defa 0
        'location',//['department','store']
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function user()
    {
        return  $this->belongsTo(User::class)->withTrashed();
    }
    public function stock()
    {
        return  $this->belongsTo(Stock::class)->withTrashed();
    }
    public function branch()
    {
        return  $this->belongsTo(Branch::class)->withTrashed();
    }
}
