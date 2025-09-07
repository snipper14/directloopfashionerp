<?php

namespace App\StockTransfer;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransfer extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'stock_id',
        'branch_id',
        'department_id',
        'branch_to_id',
        'user_id',
        "note",
        'qty',
       "transfer_date",
       'transfer_code',
       'reference_code',
       "status",//['pending','completed'
       "receive_status",//['pending','received'
       'received_date',
       'received_qty',
       'receiver_id',
       'received_department_id'
    ];
    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock'=>'']);
    }
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id')->withTrashed()->withDefault(['branch'=>'']);
    }
    public function branch_to(){
        return $this->belongsTo(Branch::class,'branch_to_id')->withTrashed()->withDefault(['branch_to'=>'']);
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id')->withTrashed()->withDefault(['user'=>'']);
    }
    public function receiver(){
        return $this->belongsTo(User::class,'receiver_id')->withTrashed()->withDefault(['receiver'=>'']);
    }
    public function received_department(){
        return $this->belongsTo(Department::class,'received_department_id')->withTrashed()->withDefault(['received_department'=>'']);
    }
    public function department(){
        return $this->belongsTo(Department::class,'department_id')->withTrashed()->withDefault(['department'=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
