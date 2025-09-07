<?php

namespace App\Register;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Register extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'opening_cash_balance',
        'opening_mpesa_balance',
        'user_id',
        'branch_id',
        'status',//['opened','closed']
    ];

    function user(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user'=>""]);
    }
        protected static function boot()
        {
            parent::boot();
    
            static::addGlobalScope(new BranchScope);
        }
}
