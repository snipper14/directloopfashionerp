<?php

namespace App\Employee;

use App\dept\Department;
use App\Scopes\BranchScope;
use App\Employee\EmployeeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'first_name',
        'last_name',
        'id_no',
        'dob',
        'phone',
        'email',
        'other',
        'department_id',
        'employee_type_id',
        'salary',
        'hrly_rate',
        'payment_type',
        'kra_pin',
        'job_no',
        'start_contract',
        'deduct_nssf',
        'deduct_nhif',
        'deduct_paye',
        'pay_method',
        'bank_branch',
        'bank_name',
        'account_name',
        'account_no',
        'payment_phone',
        'user_id',
        'branch_id',
        'deduct_housinglevy'
    ];
    protected $appends = ['name'];
    public  function getNameAttribute()
    {
        return  $this->first_name . ' ' . $this->last_name;
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
    }
    public function employee_type()
    {
        return $this->belongsTo(EmployeeType::class)->withTrashed()->withDefault(['employee_type' => '']);
    }
    //      protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }

}
