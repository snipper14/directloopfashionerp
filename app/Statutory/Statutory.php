<?php

namespace App\Statutory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statutory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'statutory_type',
        'salary_from',
        'salary_to',
        'amount_deducted',
        "percentage_deduction",
        'user_id',
        'branch_id',
    ];
}
