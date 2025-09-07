<?php

namespace App\Card;

use App\Scopes\BranchScope;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardTerminalAccount extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',

        'account_id',



    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id', 'id')->withDefault(['account' => ''])->withTrashed();
    }
}
