<?php

namespace App\PurchaseOrder;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderTemp extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',

        'branch_id',
        'vendor_reference',

        'shipping_address',
        'order_deadline',
        'receipt_date',
        'stock_id',
        'qty',

        'unit_price',
        'taxes',
        'tax_rate',
        'sub_total'

    ];

   public static function fetch()
    {
        $data = self::with(['user', 'stock', 'supplier'])->get();
        return $data;
    }
    public static function fetchGrouped()
    {
        $data = self::groupBy('supplier_id')->get();
        return $data;
    }
    

    public function user()
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
