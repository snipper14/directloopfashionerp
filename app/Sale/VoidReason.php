<?php

namespace App\Sale;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class VoidReason extends Model
{
    //

    protected $fillable=[
        
        'branch_id',
        'user_id',
        "details"
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
