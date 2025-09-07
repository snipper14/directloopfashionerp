<?php

namespace App\salary_deductions;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'payroll_id',
        'loan_id',
        'employee_id',
        'amount_paid',
        'payroll_date'
    ];
}
