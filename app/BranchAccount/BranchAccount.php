<?php

namespace App\BranchAccount;

use App\Branch\Branch;
use App\Ledgers\LedgerAccount;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchAccount extends Model
{
    protected $fillable=[
        'account_id',
        'branch_id',
        'user_id'
    ];

    function account(){
        return $this->belongsTo(LedgerAccount::class)->withDefault(['account'=>'']);
    }
    function branch(){
         return $this->belongsTo(Branch::class)->withDefault(['branch'=>'']);
    }
    function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
}
