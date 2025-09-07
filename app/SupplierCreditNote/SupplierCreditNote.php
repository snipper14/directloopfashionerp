<?php

namespace App\SupplierCreditNote;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\SupplierInvoice\SupplierInvoice;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierCreditNote extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'credit_no',
        'branch_id',
        'supplier_id',
        'supplier_invoice_id',
        'user_id',
        'invoice_id',
        'credit_amount',
        'invoice_total',
        'details',
        'credit_date'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class)->withDefault(['supplier'=>''])->withTrashed();
    }
    public function supplier_invoice(){
        return $this->belongsTo(    SupplierInvoice::class)->withDefault(['supplier_invoice'=>''])->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
    }
    public function branch(){
        return $this->belongsTo(Branch::class)->withTrashed();
    }
}
