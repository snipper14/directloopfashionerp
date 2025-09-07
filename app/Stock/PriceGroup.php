<?php

namespace App\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceGroup extends Model
{
    use SoftDeletes;
    protected $fillable =[
        'price_group'
    ];
}
