<?php

namespace App\Allowance;

use App\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allowance extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'employee_id',
        'amount',
        'allowance_type'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
