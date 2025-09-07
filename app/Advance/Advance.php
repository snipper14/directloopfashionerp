<?php

namespace App\Advance;

use App\Employee\Employee;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'employee_id',
        'payment_month',
        'amount',
        'isDeducted',
        'payroll_code',
        'payroll_account_id',
        'ref_no'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function payroll_account()
    {
        return $this->belongsTo(LedgerAccount::class, 'payroll_account_id')->withTrashed()->withDefault(['payroll_account'=>'']);
    }
}
