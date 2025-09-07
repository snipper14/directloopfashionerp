<?php

namespace App\Leave;

use App\User;
use App\Employee\Employee;
use App\Employee\LeaveType;
use App\Employee\EmployeeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'user_id',
        'start_from',
        'end_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withDefault(['employee' => ''])->withTrashed();
    }
    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class)->withDefault(['leave_type' => ''])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
