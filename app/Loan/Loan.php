<?php

namespace App\Loan;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'branch_id',
        'employee_id',
        'description',
        'loan_date',
        'loan_amount',
        'installment_amount',
        'outstanding_balance',
        'completion_date',
        'percent_interest',
        'loan_period',
        'loan_type',
        'total_payable_amount',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
