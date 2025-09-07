<?php

namespace App\POS;

use App\Branch\Branch;
use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Location\Table;
use App\Customer\Customer;
use App\dept\Department;
use App\Location\Location;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [

        'branch_id',
        'user_id',
        'stock_id',
        'qty',
        'selling_price',
        'row_amount',
        'row_vat',
        'order_date',
        'order_no',
        'tax_rate',
        'product_name',
        'code',
        'customer_id',
        'customer_name',
        'batch_no'
    ];
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch' => '']);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => ""]);
    }


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(['customer' => ""]);
    }


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
