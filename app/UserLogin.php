<?php

namespace App;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
  protected $fillable=[
    'location_ip',
    'user_id',
    'branch_id',
    'module',
  ];

  function user(){
      return $this->belongsTo(User::class)->withTrashed()->withDefault(["user"=>""]);
  }
  protected static function boot()
  {
      parent::boot();

      static::addGlobalScope(new BranchScope);
  }
}
