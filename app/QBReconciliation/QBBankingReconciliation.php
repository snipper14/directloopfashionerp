<?php

namespace App\QBReconciliation;

use App\User;
use App\Branch\Branch;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QBBankingReconciliation extends Model
{
   use SoftDeletes;
    protected $fillable = [
        'general_ledger_id',
        'account_id',
        'entry_date',
        'status', //['completed','pending','uncleared'])->default('pending');
        'dr_amount',
        'cr_amount',
        'opening_balance',
        'closing_balance',
        'ref',
        'user_id',
        'branch_id',
        'details'
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id', 'id')->withDefault(['account' => ''])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    function generalLedger(){
        return $this->belongsTo(GeneralLedger::class)->withTrashed()->withDefault(['general_ledger'=>'']);
    }
}
