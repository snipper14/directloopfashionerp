<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\dept\Department;
use App\Customer\Customer;
use App\Sale\TakeAwaySale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TakeAwaySoldItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'unit_id',
        'customer_id',
        'department_id',
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
        'take_away_sale_id',
        'order_type',
    ];

    function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            "department" => '',

        ]);
    }
    public function take_away_sale()
    {
        return $this->belongsTo(TakeAwaySale::class)->withTrashed();
    }
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
}
