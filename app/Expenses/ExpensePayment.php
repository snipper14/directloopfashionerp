<?php

namespace App\Expenses;

use App\Branch\Branch;
use App\Ledgers\LedgerAccount;
use App\User;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpensePayment extends Model
{
    use SoftDeletes;
    protected $fillable=[
    'expense_id',
        'supplier_id',
        'account_id',
        'user_id',
        'branch_id',
        'expense_amount',
        'amount_paid',
        "ref_no",
        'date_paid',
       'payment_details',
       'pay_method',
       'deleted_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class)->withDefault(['supplier'=>''])->withTrashed();
    }
    public function expense(){
        return $this->belongsTo(Expenses::class)->withDefault(['expense'=>''])->withTrashed();
    }
    public function branch(){
         return $this->belongsTo(Branch::class)->withDefault(['branch'=>''])->withTrashed();
    }
    public function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
    }
    public function account(){
        return $this->belongsTo(LedgerAccount::class)->withDefault(['account'=>''])->withTrashed();
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();
    
        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);
    
        // Perform soft delete
        static::where($conditions)->delete();
    }
}
