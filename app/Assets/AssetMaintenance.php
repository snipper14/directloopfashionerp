<?php

namespace App\Assets;

use App\User;
use App\Assets\Asset;
use App\Branch\Branch;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenance extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'user_id',
        'branch_id',
        'asset_id',
        'priority',
        'type',
        'description',
       'maintenance_start_date'
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
}
