<?php

namespace App\Shift;

use Illuminate\Database\Eloquent\Model;

class AttendanceShift extends Model
{
    protected $fillable=[
        'user_id',
        'branch_id',
        'shift_start',
        'end_shift',
        'status',
    ];
}
