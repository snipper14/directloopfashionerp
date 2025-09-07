<?php

namespace App\Promo;

use App\Traits\TracksDeletions;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable=[
        'user_id',
        'branch_id',
       'lower_limit',
       'upper_limit',
       'discount',
       'deleted_by',
        'status',//['active','inactive'])->default('active');
    ];
    protected static function boot()
    {
        parent::boot();
        static::bootTracksDeletions();
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();

        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);

        // Perform soft delete
        static::where($conditions)->delete();
    }
    function user(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => "user"]);
    }
}
