<?php

namespace App\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartAccount extends Model
{
    use SoftDeletes;
    protected $fillable =[
        'name','description','type'
    ];
}
