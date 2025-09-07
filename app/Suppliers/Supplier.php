<?php

namespace App\Suppliers;

use App\Branch\Branch;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_name',
        'company_phone',
        'address',
        'postal_address',
        'city',
        'country',
        'company_email',
        'bank_name',
        'bank_branch',
        'account_name',
        'account_no',
        'contact_person',
        'contact_email',
        'contact_phone',
        'branch_id',
        'pin',
        'credit_limit',
        'deduct_wht'//[deduct,omit]

    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
}
