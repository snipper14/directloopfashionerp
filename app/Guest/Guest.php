<?php

namespace App\Guest;

use App\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'branch_id',
        'customer_id',
        'guest_company_id',
        'phone',
        'name',
        'email',
        'gender', // ['Male', 'Female']);
        'age',
        'id_no',
        'members',
        'postal_address',
        'details',
        'country_origin',

        'company_name',
        'birth_date'
    ];
    function guest_company(){
        return $this->belongsTo(GuestCompany::class)->withTrashed()->withDefault(['guest_company'=>""]);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
}
