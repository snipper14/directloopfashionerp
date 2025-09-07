<?php

namespace App\Category;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'name',
        'details'
    ];
}
