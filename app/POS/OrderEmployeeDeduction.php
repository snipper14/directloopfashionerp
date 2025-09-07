<?php

namespace App\POS;

use App\User;
use App\Stock\Stock;
use App\Employee\Employee;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderEmployeeDeduction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'employee_id',
        'stock_id',
        'unit_id',
        'qty',
        'price',
        'row_total',
        'row_vat',
        'order_date',
        'order_no',
        'order_type',
        'cashier_id',
        'has_deducted', //[0,1]
        'clear_status',//[pending,deducted,paid]
    ];
    function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
