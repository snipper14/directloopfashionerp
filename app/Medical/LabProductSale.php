<?php

namespace App\Medical;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabProductSale extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'stock_id',
        'lab_result_id',
        'branch_id',
        'user_id',
        'customer_id',
       "product_name",
       "entry_code",
        
       "note",
        'qty',
       
       "status",//['pending','completed']
    ];
    
    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    function customer()
    {
        return $this->belongsTo(Customer::class)->default(['customer' => ""])->withTrashed();
    }
    function user()
    {
        return $this->belongsTo(User::class)->default(['user' => ""])->withTrashed();
    }
    function branch()
    {
        return $this->belongsTo(Branch::class)->default(['branch' => ""])->withTrashed();
    }
   
}
