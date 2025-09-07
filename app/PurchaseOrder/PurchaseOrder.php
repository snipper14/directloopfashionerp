<?php

namespace App\PurchaseOrder;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [

        'supplier_id',
        'user_id',
        'approver_id',
        'branch_id',
        'vendor_reference',
        'order_no',
        'shipping_address',
        'order_deadline',
        'receipt_date',
        'stock_id',
        'qty',
        'qty_delivered',
        'unit_price',
        'taxes',
        'tax_rate',
        'sub_total',
        'progress',
        'internal_confirmation',
        'supplier_confirmation',
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function approver()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
