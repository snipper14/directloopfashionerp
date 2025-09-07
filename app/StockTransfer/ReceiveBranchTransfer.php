<?php

namespace App\StockTransfer;

use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceiveBranchTransfer extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        "transfer_code",
        "received_date",
        'qty',
        'department_id',
        'stock_id'
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
function department()
    {
        return $this->belongsTo(Department::class, 'department_id')->withTrashed()->withDefault([
            'department' => 'N/A'
        ]);
    }
    function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id')->withTrashed()->withDefault([
            'stock' => 'N/A'
        ]);
    }
    function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withDefault([
            'branch' => 'N/A'
        ]);
    }
    function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'user' => 'N/A'
        ]);
    }
}
