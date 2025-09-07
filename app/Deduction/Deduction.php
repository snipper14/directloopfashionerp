<?php

namespace App\Deduction;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deduction extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id','user_id',
        "deduction_type",'employee_id',
        "monthy_deduction","deduction_total",
        'effective_date',
        'end_date','no_of_months',
        'outstanding_balance'
     ];
 
     public function employee(){
         return $this->belongsTo(Employee::class)->withDefault(['employee'=>""]);
     }
}
