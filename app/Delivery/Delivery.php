<?php

namespace App\Delivery;

use App\Branch\Branch;
use App\User;
use App\Stock\Stock;
use App\Customer\Customer;
use App\Invoices\Invoice;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable=[
        'delivery_no',
        'qty',
        "delivery_date",
        'user_id',
        'stock_id',
        'customer_id',
        'branch_id',
        'invoice_no',
        'invoice_id'
    ];
    
    public function user(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["user"=>'']);
    }
    public function stock(){
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function customer(){
        return $this->belongsTo(Customer::class)->withTrashed();
    }
    public function branch(){
        return $this->belongsTo(Branch::class)->withTrashed();
    }
    public function invoice(){
        return $this->belongsTo(Invoice::class)->withDefault(["invoice"=>""]);
    }
}
