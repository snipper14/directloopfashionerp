<?php

namespace App\TimeLog;

use App\User;
use App\Branch\Branch;
use App\Employee\Employee;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'logged_on', 'employee_id', 'no_hours', 'time_in', 'time_out', 'total_amount', 'payroll_code', 'branch_id', 'user_id'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
}
