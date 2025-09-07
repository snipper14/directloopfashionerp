<?php

namespace App\Stock;

use Illuminate\Database\Eloquent\Model;

class StockMovtStatus extends Model
{
    protected $fillable=[
        'updated',
        'location',
        'user_id',
        'branch_id',
        'date_recorded'
    ];
}
