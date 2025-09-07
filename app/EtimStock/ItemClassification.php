<?php

namespace App\EtimStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemClassification extends Model
{
    use SoftDeletes;
    protected $fillable=[
       "description",
       "code",
       'name'
    ];
}
