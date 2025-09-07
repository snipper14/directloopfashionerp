<?php

namespace App\Payroll;

use App\Employee\Employee;
use App\PayrollProduct\PayrollProduct;
use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollProduction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'produced_on', 'production_cost', 'stock_id',
        'employee_id', 'qty', 'size', 'colour', 'description', 'total_pay', 'user_id', 'branch_id'
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
}
