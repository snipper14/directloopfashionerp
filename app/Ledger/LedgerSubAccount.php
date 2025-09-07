<?php

namespace App\Ledger;

use App\Branch\Branch;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerSubAccount extends Model
{
    use  SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'account_type',
        'category',
        'user_id',
        'branch_id',
        "account_no"
    ];

    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => '']);
    }

    function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch' => '']);
    }
    
}
