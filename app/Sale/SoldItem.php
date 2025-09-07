<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Location\Table;
use App\dept\Department;
use App\Customer\Customer;
use App\Location\Location;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class SoldItem extends Model
{
    protected $fillable = [
        'order_no',
        'stock_id',
        'unit_id',
        'customer_id',
        'user_id',
        'cashier_id',
        'branch_id',
        'location_id',
        'department_id',
        'table_id',
        'order_date',
        'qty',
        'price',
        'cost_price',
        'cost_total',
        'row_total',
        'row_vat',
        'order_type',
        'prev_order_no',
        'accompaniment_id'
    ];
    public function accompaniment()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock" => ""]);
    }
    function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            "department" => '',

        ]);
    }
    public function table()
    {
        return $this->belongsTo(Table::class)->withTrashed()->withDefault(["table" => ""]);
    }
    public function location()
    {
        return $this->belongsTo(Location::class)->withTrashed()->withDefault(["location" => ""]);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock" => ""]);
    }
    public function  unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
