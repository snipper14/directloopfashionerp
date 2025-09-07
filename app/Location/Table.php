<?php

namespace App\Location;

use App\dept\Department;
use App\POS\POSOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'department_id',
        'name',
        'seat_type',
        'no_seats',
        'details'
    ];

    public function department(){
        return $this->belongsTo(Department::class)->withDefault(['department'=>""])->withTrashed();
    }
    public function orders(){
        return $this->hasMany(POSOrder::class)->groupBy('order_no');
    }
}
