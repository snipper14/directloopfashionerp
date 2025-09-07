<?php

namespace App\salary_deductions;

use Illuminate\Database\Eloquent\Model;

class DeductionPayment extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'payroll_id',
        'deduction_id',
        'employee_id',
        'amount_paid',
        'payroll_date'
    ];
}
