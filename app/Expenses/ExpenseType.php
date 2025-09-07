<?php

namespace App\Expenses;

use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseType extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'name',
        'account_no',
        'ledger_account_id'
    ];
    protected $appends = ['original_name'];
    public function getNameAttribute($value)
{
    return $this->account_no ? "{$value}" : $value;
}
public function getOriginalNameAttribute()
{
    return $this->attributes['name'];
}
public function ledgerAccount(){
    return $this->belongsTo(LedgerAccount::class,'ledger_account_id')->withTrashed()->withDefault(["ledger_accounts"=>""]);
}

}
