<?php

namespace App\logs;

use App\dept\Department;
use App\Scopes\BranchScope;
use App\Stock\Stock;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLog extends Model
{
    
    protected $fillable = [
        'inventory_id',
        'stock_id',
        'department_id',
        'branch_id',
        'change_qty',
        'new_qty',
        'action_type',
        'user_id'
    ];
      protected $appends = ['created_at_nairobi','updated_at_nairobi'];

     public function getCreatedAtNairobiAttribute()
    {
        $raw = $this->getRawOriginal('created_at'); // e.g. "2025-08-19 05:14:37" (UTC)
        if (!$raw) return null;

        return Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'UTC')
            ->setTimezone('Africa/Nairobi')
            ->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtNairobiAttribute()
    {
        $raw = $this->getRawOriginal('updated_at');
        if (!$raw) return null;

        return Carbon::createFromFormat('Y-m-d H:i:s', $raw, 'UTC')
            ->setTimezone('Africa/Nairobi')
            ->format('Y-m-d H:i:s');
    }

    protected static function boot()
    {
        parent::boot();

        // Add global scope
        static::addGlobalScope(new BranchScope);
    }

    function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => ""]);
    }

    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => ""]);
    }
    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }
}
