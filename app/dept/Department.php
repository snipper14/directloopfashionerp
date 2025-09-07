<?php

namespace App\dept;

use App\Branch\Branch;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'department',
        'branch_id',
        'user_id',
        'show_pos' //boolean default false
        , 'details',
        "location_type", //['virtual','physical']
    ];
    //
    function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch'=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
