<?php

namespace App\Ledgers;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Ledgers\LedgerAccount;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedger extends Model
{
    use SoftDeletes, TracksDeletions;
    protected $fillable = [
        'branch_id',
        'user_id',

        "other_account_name",

        'account_id',

       
        "account_type",

        'debit_amount',
        'credit_amount',
        "details",
        "ref",
        "entry_code",
        "status", //["completed","pending"]
        "entry_date",
        'unique_code',
        'deleted_by',
        'unique_id'
    ];
   

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    protected static function boot()
    {
        parent::boot();
        static::bootTracksDeletions();
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();

        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);

        // Perform soft delete
        static::where($conditions)->delete();
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
