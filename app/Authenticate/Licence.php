<?php

namespace App\Authenticate;

use Illuminate\Database\Eloquent\Model;

class Licence extends Model
{
    protected $fillable = [
        "expiry_date"
    ];
}
