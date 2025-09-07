<?php

namespace App\Ledger;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditLedger extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable = [
        'user_id',
        'supplier_id',
        'branch_id',
        'ref',
        'type',
        'entry_date',
        'description',
        'debit',
        'credit',
        'amount_due',
        'balance',
        "deleted_by",
        'unique_id'
    ];
    
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
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
