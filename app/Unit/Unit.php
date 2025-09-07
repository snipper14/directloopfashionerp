<?php

namespace App\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 
        'code',
        'description',
        'user_id',
        'isSync'//[0,1]
    ];
}
