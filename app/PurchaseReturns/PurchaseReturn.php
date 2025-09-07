<?php

namespace App\PurchaseReturns;

use App\Stock\Stock;
use App\Suppliers\Supplier;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'stock_id',
        'branch_id',
        "qty",
        'return_code',
        'order_no',
        "details",
        'return_date',
        'supplier_id',
        'unit_price',
        'opening_stock',
        'department_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }
}
