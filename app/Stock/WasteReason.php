<?php

namespace App\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteReason extends Model
{
  //  use SoftDeletes;
    protected $fillable=[
       "reasons",
        'branch_id',
        'user_id'
    ];

    
}
