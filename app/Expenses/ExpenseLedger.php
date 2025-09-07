<?php

namespace App\Expenses;

use App\User;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseLedger extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable=[
        'user_id',
        'expense_id',
        'branch_id',
        'ref',
        'type',
        'entry_date',
        'description',
        'debit',
        'credit',
        "deleted_by",'unique_id'
      
    ];
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
    public function supplier(){
        return $this->belongsTo(Supplier::class)->withDefault(['supplier'=>''])->withTrashed();
    }
    public function expense(){
        return $this->belongsTo(Expenses::class)->withDefault(['expense'=>''])->withTrashed();
    }
    public function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
    }
    public function branch(){
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
