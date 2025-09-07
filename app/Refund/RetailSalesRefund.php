<?php

namespace App\Refund;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetailSalesRefund extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'account_id',
        'branch_id',
        'user_id',
       'refund_amount',
        'ref_details',
        'comments',
        'receipt_no',
        'credit_no',
       'credit_date',
       'pay_method'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id','id')->withDefault(['account'=>''])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
}
