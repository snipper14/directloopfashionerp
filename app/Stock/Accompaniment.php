<?php

namespace App\Stock;

use App\Category\MenuCategory;
use App\Category\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accompaniment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'accompaniment_id',
        'category_id',
        'category'
    ];


    public function accompaniment()
    {
        return $this->belongsTo(Stock::class)->withDefault(["accompaniment" => ""]);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(["stock" => ""]);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class)->withDefault(["category" => ""]);
    }
}
