<?php

namespace App\Stock;

use App\User;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class  PhysicalStock extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'department_id',
        'opening_stock',

        'qty', 
        'type',
        'stock_id',
        'original_qty','selling_price','purchase_price'
    ];

    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }

    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => '']);
    }
    function department(){
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
