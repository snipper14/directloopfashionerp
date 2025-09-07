<?php

namespace App\Assets;

use App\User;
use App\Assets\Asset;
use App\Branch\Branch;
use App\Employee\Employee;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAllocation extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'asset_id',
        'employee_id',
        'allocation_code',
        'allocated_to',
        'allocated_from',
       'qty',
       'reason',
       'allocation_status'//['allocated','revoked']
    ];
    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
 
    public function branch(){
     return $this->belongsTo(Branch::class)->withTrashed();
 }
  protected static function boot()
     {
         parent::boot();
 
         static::addGlobalScope(new BranchScope);
     }
    function asset(){
        return $this->belongsTo(Asset::class)->withDefault(['asset'=>''])->withTrashed();
    }
    function employee(){
        return $this->belongsTo(Employee::class)->withDefault(['employee'=>''])->withTrashed();
    }
}
