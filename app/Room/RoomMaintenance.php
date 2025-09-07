<?php

namespace App\Room;

use App\Rooms\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomMaintenance extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'branch_id',
        'room_id',
        'item',
        'qty',
        'price',
        'total',
        'details',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class)->withTrashed();
    }
}
