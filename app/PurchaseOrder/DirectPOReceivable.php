<?php

namespace App\PurchaseOrder;

use App\Branch\Branch;
use App\User;
use App\Stock\Stock;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectPOReceivable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'direct_purchase_order_id',
        'supplier_id',
        'stock_id',
        'user_id',
        'qty_delivered',
        'qty_ordered',
        'order_no',
        'delivery_no',
        'branch_id',
        'received_date',
        'sub_total',
        'unit_price',
        'opening_stock',
        'status'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(["branch"=>""]);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed()->withDefault(["supplier"=>""]);
    }
    public function direct_purchase_order()
    {
        return $this->belongsTo(DirectPurchaseOrder::class)->withTrashed()->withDefault(["direct_purchase_order"=>""]);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["user"=>""]);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock"=>""]);
    }
}
