<?php

namespace App\Transaction;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'ref',
        'entry_date',
        'description',
        'credit',
        'debit',
        'amount',
    ];

    public function user(){
       return $this->belongsTo(User::class)->withTrashed();
    }

    public function branch(){
       return $this->belongsTo(Branch::class)->withTrashed();
    }
}
