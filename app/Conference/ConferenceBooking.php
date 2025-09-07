<?php

namespace App\Conference;

use App\Scopes\BranchScope;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConferenceBooking extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'conference_room',
        'no_guest',
        'company_name',
        'email',
        'phone',
        'total_paid',
        "from",
        "to",
        "details",
        'receipt_no',
        "pay_method"
    ];

    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["user" => ""]);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
