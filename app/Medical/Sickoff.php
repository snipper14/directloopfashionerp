<?php

namespace App\Medical;

use App\User;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sickoff extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'customer_id',
        "age",
        "reg_no",
        "gender", //['male', 'female'],
        'sickoff_days',
        'startdate',
        'enddate',
        'currentdate',
        'doctors_name',
        'doctors_report',
        'cost',
        'patient_name',
        'entry_code'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    function customer(){
        return $this->belongsTo(Customer::class)->withDefault(['customer'=>''])->withTrashed();
    }
    function user(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user'=>'']);
    }
}
