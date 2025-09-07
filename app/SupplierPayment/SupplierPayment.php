<?php

namespace App\SupplierPayment;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\SupplierInvoice\SupplierInvoice;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'supplier_id',
        "user_id",
        'amount_paid',
        'outstanding_balance',
        "ref_no",
        'date_paid',
        'payment_details',
        'branch_id',
        'pay_method',
        "supplier_invoice_id",
        "invoice_amount"
    ];
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    public function supplier_invoice(){
        return $this->belongsTo(SupplierInvoice::class,"supplier_invoice_id")->withDefault(["supplier_invoice"=>""])->withTrashed();
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault(["supplier"=>""])->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(["user"=>""])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(["branch"=>""])->withTrashed();
    }
}
