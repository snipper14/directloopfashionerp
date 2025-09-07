<?php

namespace App\Guest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestCompany extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'name',
        'address',
        'contacts',
        'email',
    ];
}
