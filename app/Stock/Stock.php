<?php

namespace App\Stock;

use App\Unit\Unit;
use App\tax\TaxDept;
use App\Log\ModelLog;
use App\Branch\Branch;
use App\Location\Shelf;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Scopes\IsActiveScope;
use App\EtimStock\EtimsItemType;
use App\Category\ProductCategory;
use Illuminate\Support\Facades\App;
use App\EtimStock\EtimsOriginCountry;
use App\EtimStock\ItemClassification;
use Illuminate\Database\Eloquent\Model;
use App\EtimStock\EtimsItemPackagingCode;
use App\Inventory\Inventory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

//use  App\Stock\Stock;
class Stock extends Model
{
    use SoftDeletes;
    protected $fillable = [

        'user_id',
        'product_category_id',
        'unit_id',
        'tax_dept_id',
        'product_name',
        'name',

        'code',
        'qty',
        'reorder_qty',
        'selling_price',
        'wholesale_price',
        'cost_price',

        'description',
        'image',
        'show_img',

        'department_id',
        'purchase_price',
        'store_qty',
        'supplier_id',
        'is_active',
        'inventory_type', //inventory,non_inventory
        'item_id',
        'item_class_code',
        'item_type_code',
        'origin_nation_code',
        'package_unit_code',
        'quantity_unit_code',
        'tax_type_code',
        'default_unit_price',
        'item_name',
        'is_stock_item',
        'shelf_id',
        'etims_item_type_id',
        'etims_origin_country_id',
        'etims_item_packaging_code_id',
        'item_classification_id',

        'min_profit_margin',

        'hs_code',
        'etims_code',
        'item_discount'

    ];
    protected $appends = ['img_url'];
    public  function getImgUrlAttribute()
    {
        return  App::make('url')->to('/') . '/stock_img/' . $this->image;
    }
    public function inventory()
{
    return $this->hasMany(Inventory::class, 'stock_id', 'id');
}
    public function branchshelves()
    {
        return $this->hasOne(Branchshelf::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault(['supplier' => ""]);
    }
    public function etims_item_type()
    {
        return $this->belongsTo(EtimsItemType::class, 'etims_item_type_id')->withDefault(['etims_item_type' => ''])->withTrashed();
    }
    public function etims_origin_country()
    {
        return $this->belongsTo(EtimsOriginCountry::class, 'etims_origin_country_id')->withDefault(['etims_origin_country' => ''])->withTrashed();
    }

    public function etims_item_packaging_code()
    {
        return $this->belongsTo(EtimsItemPackagingCode::class, 'etims_item_packaging_code_id')->withDefault(['etims_item_packaging_code' => ''])->withTrashed();
    }
    public function item_classification()
    {
        return $this->belongsTo(ItemClassification::class, 'item_classification_id')->withDefault(['item_classification' => ''])->withTrashed();
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault(['department' => ''])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ''])->withTrashed();
    }
    public function shelf()
    {

        return $this->belongsTo(Shelf::class)->withDefault(['shelf' => ''])->withTrashed();
    }
    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class)->withDefault(['product_category' => ''])->withTrashed();
    }
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class)->withDefault(['product_category' => ''])->withTrashed();
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class)->withDefault(['unit' => ''])->withTrashed();
    }

    public function tax_dept()
    {
        return $this->belongsTo(TaxDept::class)->withTrashed()->withDefault(["tax_dept"=>""]);
    }

    public function accompaniment()
    {

        return $this->hasMany(Accompaniment::class);
    }
  public static function updateByConditions(array $conditions, array $data): bool
    {
        $records = static::where($conditions)->get();
    
        foreach ($records as $record) {
            $original = $record->getOriginal();
            $changes = array_intersect_key($data, $original);
    
            $differences = [];
            foreach ($changes as $key => $newVal) {
                if ($original[$key] != $newVal) {
                    $differences['before'][$key] = $original[$key];
                    $differences['after'][$key] = $newVal;
                }
            }
    
            if (!empty($differences)) {
                ModelLog::create([
                    'model_type' => static::class,
                    'model_id' => $record->id,
                    'action' => 'updated',
                    'data' => json_encode($differences),
                    'user_id' => Auth::id(),
                    'branch_id'=>Auth::user()->branch_id
                ]);
            }
        }
    
        return static::where($conditions)->update($data) > 0;
    }
    protected static function booted()
    {
        static::created(function ($stock) {
            ModelLog::create([
                'model_type' => get_class($stock),
                'model_id' => $stock->id,
                'action' => 'created',
                'data' => $stock->toJson(),
                'user_id' => auth()->id(),
                'branch_id' => Auth::user()->branch_id
            ]);
        });

        static::updating(function ($stock) {
            $original = $stock->getOriginal(); // Old values
            $changes = $stock->getDirty();     // New changed values

            // Always include the 'name' field in the changes log
            $logChanges = $changes;
            $logChanges['name'] = $stock->name; // Include current name value

            // Only log if something actually changed or name is present
            if (!empty($changes) || isset($stock->name)) {
                ModelLog::create([
                    'model_type' => get_class($stock),
                    'model_id' => $stock->id,
                    'action' => 'updated',
                    'data' => json_encode([
                        'before' => array_merge(
                            array_intersect_key($original, $changes),
                            ['name' => $original['name']]
                        ),
                        'after' => $logChanges
                    ]),
                    'user_id' => auth()->id(),
                    'branch_id' => Auth::user()->branch_id
                ]);
            }
        });

        static::deleted(function ($stock) {
            ModelLog::create([
                'model_type' => get_class($stock),
                'model_id' => $stock->id,
                'action' => 'deleted',
                'data' => $stock->toJson(),
                'user_id' => auth()->id(),
                'branch_id' => Auth::user()->branch_id
            ]);
        });
    }
}

