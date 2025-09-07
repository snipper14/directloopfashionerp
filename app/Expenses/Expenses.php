<?php

namespace App\Expenses;

use App\Branch\Branch;
use App\User;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expenses extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable=[
        'branch_id',
        'expense_type_id',
            'user_id',
            'amount', 
           'amount_paid', 
           'unpaid_balance', 
            'details', 
            'date_recorded',
            'supplier_id',
            'ref_no',
            "deleted_by",'unique_id',
            'cu_invoice_no'
    ];
//    protected static function boot()
//     {
//         parent::boot();

//         static::addGlobalScope(new BranchScope);
//     }
function branch(){
    return $this->belongsTo(Branch::class)->withDefault(['branch'=>'']); 
}
    public function expenseType(){
        return $this->belongsTo(ExpenseType::class)->withDefault(['expense_type'=>''])->withTrashed();
    }
    public function expense_type(){
        return $this->belongsTo(ExpenseType::class)->withDefault(['expense_type'=>''])->withTrashed();
    }
    public function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class)->withDefault(['supplier'=>''])->withTrashed();
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
         static::addGlobalScope(new BranchScope);
    }
}
