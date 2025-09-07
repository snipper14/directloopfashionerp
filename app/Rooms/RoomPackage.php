<?php

namespace App\Rooms;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomPackage extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'name',
      
        'details',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
