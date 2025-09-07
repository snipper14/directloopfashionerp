<?php

namespace App\CashDrops;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CloseRegisterHourlySale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'hours',
        'user_id',
        'branch_id',
        'close_register_id',
        'amount',
    ];
    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
