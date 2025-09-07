<?php

namespace App\Customer;

use App\Stock\PriceGroup;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
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
        'acc_code',
        'contact_person',
        'contact_email',
        'contact_phone',
        'branch_id',
        'pin',
        'credit_limit',
        'price_group_id',
        'vat_deduction',//['vatable','exempted']
        'etims_id',
        'loyalty_program',//['active','inactive']
        'is_discount_qualified',// ['active', 'inactive']
        'loyalty_points','redeemed_loyalty_points'
    ];


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
     public static function getCreditAmount($id){
        $credit_limit=self::where('id',$id)->first()->credit_limit;
        return $credit_limit;
     }  
    function price_group()
    {
        return $this->belongsTo(PriceGroup::class)->withDefault(['price_group' => '']);
    }
}
