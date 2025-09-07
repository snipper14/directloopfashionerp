<?php

namespace App\Returns;

use App\User;
use App\Stock\Stock;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\PurchaseOrder\PurchaseOrderReceivable;

class ReturnGRN extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_id',
        'branch_id',
        'department_id',
        'user_id',
        'purchase_order_receivable_id',
        'supplier_id',
        'unit_price',
        'qty',
        'tax_rate',
        'tax_amount',
        'sub_total',
        "return_date",
        "returns_conditions",
        "return_reasons",
        "return_code",
        'delivery_no'
    ];

    function department()
    {
        return $this->belongsTo(Department::class)->withDefault(['department' => ""]);
    }
    function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault(['supplier' => ""]);
    }
    function purchase_order_receivable()
    {
        return $this->belongsTo(PurchaseOrderReceivable::class, 'purchase_order_receivable_id','id');
    }
    function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(["stock" => ""])->withTrashed();
    }
    function user()
    {
        return $this->belongsTo(User::class)->withDefault(["user" => ""])->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
