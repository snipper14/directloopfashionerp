<?php

namespace App\EtimStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtimsOriginCountry extends Model
{
    use SoftDeletes;
    protected $fillable=[
       "description",
       "code",
       'name'
    ];
}
