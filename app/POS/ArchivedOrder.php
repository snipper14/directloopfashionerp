<?php

namespace App\POS;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Location\Table;
use App\dept\Department;
use App\Customer\Customer;
use App\Location\Location;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivedOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [

        'branch_id',
        'user_id',
        'cashier_id',
        'location_id',
        'table_id',
        'department_id',
        'stock_id',
        'unit_id',
        'customer_id',
        'qty',
        'price',
        'row_total',
        'row_vat',
        'no_guest',
        'notes',
        'order_date',
        'order_no',
        'prev_order_no',
        'merged_order_no',
        'isComplete', //['1','0'])
        'order_type',
        'cost_price',
        'cost_total',
        'orderPrinted', //['1','0']
        'accompaniment_id',
        'isBillPrinted', //['1','0']
        'deleted_at',
        'archived_at',
        'customer_name',
        'customer_address',
        'customer_phone',
        'delivery_time'
        

    ];

    public function table()
    {
        return $this->belongsTo(Table::class)->withTrashed()->withDefault(['table' => ""]);
    }
    public function location()
    {
        return $this->belongsTo(Location::class)->withTrashed()->withDefault(['location' => ""]);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => ""]);
    }
    public function  unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed()->withDefault(['unit' => ""]);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed()->withDefault(['customer' => ""]);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            'department' => "",

        ]);;
    }
    public function accompaniment()
    {
        return $this->belongsTo(Stock::class)->withDefault([
            'accompaniment' => "",

        ]);;
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }

}
