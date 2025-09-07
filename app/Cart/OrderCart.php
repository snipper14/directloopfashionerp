<?php

namespace App\Cart;

use App\Customer\Customer;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;

class OrderCart extends Model
{
    protected $fillable=[
       'order_no',
       'stock_id',
       'department_id',
       'customer_id',
       'user_id',
       'branch_id',
       'selling_price',
       'row_total',
       'order_date',
       'qty',
      'tax_amount',
      'purchase_price',
      'discount',
      'product_category_id',
      'batch_no'
    ];

    public function stock(){
      return  $this->belongsTo(Stock::class)->withTrashed();
    }
    public function customer(){
      return  $this->belongsTo(Customer::class)->withTrashed();
    }
    public function user(){
       return $this->belongsTo(User::class)->withTrashed();
    }
}
