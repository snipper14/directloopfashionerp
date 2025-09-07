<?php

namespace App\Ingredient;

use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
   protected $fillable=[
   'stock_id',
   'user_id',
   'ingredient_id',
    'qty',
    'produced_qty',
    'issue_no',
    'department_id'
   ];

   function ingredient(){
       return $this->belongsTo(Stock::class);
   }
   function stock(){
       return $this->belongsTo(Stock::class);
   }
   function user(){
       return $this->belongsTo(User::class);
   }
}
