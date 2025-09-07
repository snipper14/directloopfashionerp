<?php

namespace App\TakeAway;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\dept\Department;
use App\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TakeawayOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'unit_id',
        'customer_id',
        'qty',
        'price',
        'row_total',
        'row_vat',
        'vat_rate',
        'cost_price',
        'cost_total',
        'notes',
        'order_date',
        'order_no',
        'isComplete', //['1','0'])->default('0');
        'order_type',
        'department_id'
    ];


    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function  unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            'department' => "",

        ]);;
    }
}
