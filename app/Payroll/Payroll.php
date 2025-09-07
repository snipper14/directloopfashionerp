<?php

namespace App\Payroll;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{

    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'employee_id',
        'payroll_from',
        'payroll_to',
        'basic_salary',
        'total_allowance', 
        'total_rewards',
        'nssf', 
        'nhif',
        'income_tax', 
        'tax_relief',
        'net_paye',
        'housing_levy',
        'pay_after_tax', 
        'total_advance',
        'total_loans',
        'total_deduction', 
        'total_overtime', 
        'net_salary', 
        'payment_type',
        'no_hrs',
        'payroll_code',
        'insurance_relief',
        'nssf_tier1',
        'nssf_tier2',
        'housing_levy_relief'
    ];
    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed()->withDefault(["employee"=>""]);
    }
}
