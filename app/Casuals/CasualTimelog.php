<?php

namespace App\Casuals;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CasualTimelog extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'employee_id',
        'no_hrs',
        'hrly_rate',
        'amount',
        'time_in',
        'time_out',
        'date_recorded',
        'isPaid',
        'payroll_code'
      ];
  
      public function employee(){
          return $this->belongsTo(Employee::class);
      }
}
