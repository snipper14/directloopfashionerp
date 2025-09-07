<?php

namespace App\PayrollProduct;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollProduct extends Model
{
   use SoftDeletes;

   protected $fillable=[
    'name','product_code','description','size','pay_rate','user_id','branch_id'
];
}
