<?php

namespace App\TableBooking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableBooking extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "name",
        "email",
        "phone",
        "event_date",
        "no_people",
        "details"

    ];
}
