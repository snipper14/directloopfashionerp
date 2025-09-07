<?php

namespace App\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'name',
        'details'
    ];
}
