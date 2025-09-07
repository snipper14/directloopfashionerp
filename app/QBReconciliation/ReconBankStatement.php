<?php

namespace App\QBReconciliation;

use App\User;
use App\Branch\Branch;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReconBankStatement extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'general_ledger_id',
        'account_id',
        'transaction_date',
        'credit_amount',
        'debit_amount',
        'opening_balance',
        'closing_balance',
        'ref_no',
        'user_id',
        'branch_id',
        'details',
        "status", //['pending','reconciled']
    ];

    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id', 'id')->withDefault(['account' => ''])->withTrashed();
    }
    public function generalLedger()
    {
        return $this->belongsTo(GeneralLedger::class, 'general_ledger_id', 'id')->withDefault(['general_ledgers' => ''])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
