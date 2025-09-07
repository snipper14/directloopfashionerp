<?php

namespace App\Log;

use App\Branch\Branch;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'data',
        'user_id',
        'branch_id'
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
