<?php

namespace App\PurchaseOrder;

use App\Branch\Branch;
use App\User;
use App\Stock\Stock;
use App\dept\Department;
use App\Scopes\BranchScope;

use App\Suppliers\Supplier;
use App\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReceivable extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'purchase_order_id',
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
        'status',
        'department_id',
        'cu_invoice_no',
        'tax_rate',
        'tax_amount',
        'qty_sold',
        'batch_no',
        'expiry_date',
        'discount_amount'
    ];
public function branch()
{
    return $this->belongsTo(Branch::class);
}
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }
    public function purchase_order()
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
