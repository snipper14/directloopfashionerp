<?php

namespace App\Rooms;

use App\Rooms\RoomStandard;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'room_standard_id',
       'door_name',
       'qty_bed',
       'floor_no',
       'details',
       'occupation',//['accupied','empty']
       'usage_status',//,['ready','not_ready']
       'is_under_maintenance',//['0','1']
      
       
    ]
    ;
    public function room_standard(){
        return $this->belongsTo(RoomStandard::class)->withTrashed();
    }
   
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
