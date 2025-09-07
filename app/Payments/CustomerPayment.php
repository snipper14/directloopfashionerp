<?php

namespace App\Payments;

use App\User;
use App\Branch\Branch;
use App\Invoices\Invoice;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPayment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'user_id',
        'branch_id',
        'amount_paid',
        'amount_due',
        "ref_no",
        'date_paid',
        'payment_details',
        'pay_method',
        "invoice_id",
        'invoice_amount'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }

    public function user(){
        return $this->belongsTo(User::class)->withDefault(["user"=>""])->withTrashed();
    }
    public function customer(){
        return $this->belongsTo(Customer::class)->withDefault(["customer"=>""])->withTrashed();
    }

    public function branch(){
        return $this->belongsTo(Branch::class)->withDefault(["branch"=>""])->withTrashed();
    }
    public function invoice(){
        return $this->belongsTo(Invoice::class)->withDefault(["invoice"=>""])->withTrashed();
    }
}
