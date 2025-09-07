<?php

namespace App\Employee;

use App\Branch\Branch;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeType extends Model
{
use SoftDeletes;
protected $fillable=[
    'branch_id',
    'user_id',
    'name',
    'details'
];

public function branch(){
   return $this->belongsTo(Branch::class)->withTrashed();
}

public function user(){
    return $this->belongsTo(User::class)->withTrashed();
}
}
