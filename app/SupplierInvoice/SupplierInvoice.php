<?php

namespace App\SupplierInvoice;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoice extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'supplier_id',
        'user_id',
        'invoice_no',
        'branch_id',
        "po_no",
        'invoice_total',
        'invoice_date',
        'description',
        'unpaid_amount',
        'paid_amount'
    ];
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    public function supplier(){
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function branch(){
        return $this->belongsTo(Branch::class)->withTrashed();
    }
}
