<?php

namespace App\Assets;

use App\User;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
   use SoftDeletes;
   protected $fillable=[
    'department_id',
    'asset_code',
    'asset_name',
    'qty',
    'model_name',
    'serial_no',
    'asset_category_id',
    'purchase_date',
    'purchase_price',
    'purchase_type',
   'branch_id',
   'user_id',
    'monthly_depreciation',
    'description',
   ];

   function asset_category(){
    return $this->belongsTo(AssetCategory::class)->withDefault(['asset_category'=>''])->withTrashed();
   }
   function user(){
    return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
   }
   public function department()
   {
       return $this->belongsTo(Department::class)->withDefault(['department'=>''])->withTrashed();
   }
     protected static function boot()
{
    parent::boot();

    static::addGlobalScope(new BranchScope);
}
}
