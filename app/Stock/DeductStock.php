<?php

namespace App\Stock;

use App\User;
use App\Stock\Stock;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeductStock extends Model
{
   
    protected $fillable = [
        'user_id',
        'branch_id',
        'department_id',
         'qty', 
        'stock_id',
        'unique_code'
    ];

    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }

    function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => '']);
    }
    function department(){
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
