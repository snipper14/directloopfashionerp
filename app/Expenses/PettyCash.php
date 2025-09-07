<?php

namespace App\Expenses;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    protected $fillable=[
        'user_id',
        'opening_balance',
        'closing_balance',
       'date_recorded'
    ];

    public function user(){
        return $this->belongsTo(User::class)->withDefault(['user'=>''])->withTrashed();
    }
}
