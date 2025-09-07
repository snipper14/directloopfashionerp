<?php

namespace App\Stock;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\dept\Department;
use App\Stock\WasteReason;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockWaste extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_id',
        'unit_id',
        'department_id',
        'staff_id',
        'qty',
        'waste_reason_id',
        'branch_id',
        'user_id',
        "details",
        "waste_no",
        "purchase_price",
        "total",
        "date_wasted",
        'status' //['progress','completed'])->default('progress');
    ];
    public function department()
    {
        return  $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
    }
    public function waste_reason()
    {
        return  $this->belongsTo(WasteReason::class,'waste_reason_id');//->withTrashed()->withDefault(['waste_reason' => '']);
    }
    public function user()
    {
        return  $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => '']);
    }

    public function staff()
    {
        return  $this->belongsTo(User::class)->withTrashed()->withDefault(['staff' => '']);
    }
    public function stock()
    {
        return  $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
    public function unit()
    {
        return  $this->belongsTo(Unit::class)->withTrashed()->withDefault(['unit' => '']);
    }
     protected static function boot()
    {
        parent::boot();
    
        // Add global scope
        static::addGlobalScope(new BranchScope);
    
        // Boot additional functionalities
      
    }
}
