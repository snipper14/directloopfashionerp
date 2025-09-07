<?php

namespace App\Commission;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'employee_id',
        'payment_month',
        'type',
        'description',
        'amount',
        'payroll_code',
        'isPaid'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}
