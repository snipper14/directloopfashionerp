<?php

namespace App\Ledger;

use App\User;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Ledgers\LedgerAccount;
use App\Scopes\BranchScope;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable = [
        'user_id',
        'branch_id',
        'customer_id',
        'fk_user_id',
        'ref',
        'type',
        'entry_date',
        'description',
        'account_id',
        
        'debit',
        'credit',
        'amount_due',
        'balance',
        'unique_code',
        "deleted_by",'unique_id'
    ];
    public static function fetchCustomerBalance($customerId){
        $res = self::selectRaw('*,SUM(debit-credit) as current_balance')
            ->where('customer_id',$customerId)->orderBy('id', 'DESC')->first();

       $total=0;
       if($res->current_balance){
        $total=$res->current_balance;
       }
  return $total;
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    public function account()
    {
        return $this->belongsTo(LedgerAccount::class,'account_id')->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed()->withDefault(['customer'=>'']);
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();
    
        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);
    
        // Perform soft delete
        static::where($conditions)->delete();
    }
    protected static function boot()
    {
        parent::boot();
     
        static::bootTracksDeletions();
    }
}
