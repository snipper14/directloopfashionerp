<?php

namespace App\Ingredient;

use App\User;
use App\Stock\Stock;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class IngredientProduction extends Model
{
    protected $fillable = [
        'stock_id',
        'user_id',
        'branch_id',
        'material_id',
        'material_qty',
        'material_cost',
        'branch_id',
        'desired_qty',
        'cost_price',
        'total_materials_cost',
        'issue_no',
        'department_id',
        'production_date',
        'production_no',
        'production_status', //['progress','completed']
        'end_product' //['true','false']
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function material()
    {
        return $this->belongsTo(Stock::class)->withDefault(['material' => ''])->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(['stock' => ''])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ''])->withTrashed();
    }
}
