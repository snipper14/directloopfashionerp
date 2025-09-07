<?php

namespace App\Location;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shelf extends Model
{
    Use SoftDeletes;
    protected $fillable=[
        "name",
        "description",
        'user_id',
        'branch_id',
      
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
