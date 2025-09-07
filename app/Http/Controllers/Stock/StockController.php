<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Stock\StockMovt;
use App\Stock\StockTake;
use Illuminate\Http\Request;
use App\Stock\PriceGroupAmount;
use App\Category\ProductCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\StockRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StockController extends BaseController
{
    public function createUpload(StockRequest $request)
    {
        $product_code = $request->code ? $request->code : $this->productCode();
        Stock::create(['code' => $product_code] + $request->validated() + [
            'branch_id' => Auth::user()->branch_id,
            'user_id' => Auth::user()->id,
        ]);
        return true;
    }
    public function create(Request $request)
    {

        $data = json_decode($request['data'], true);
        $product_code = $data['code'] ? $data['code'] : $this->productCode();
        if ($request->file != "null") {
            $request->validate([
                'file' => 'mimes:jpeg,jpg,png,webp,gif|required|max:10000'
            ]);
        }
        $rules = [
            'name' => "required",
            // 'product_name' => 'required|max:200|unique_custom_ignore_deleted:stocks,product_name,branch_id,' . Auth::user()->branch_id,
            'product_name' => 'required|max:250|unique:stocks,product_name,NULL,id,deleted_at,NULL',
            'unit_id' => "required",
            'tax_dept_id' => "required",
            'product_category_id' => "required",
            'code' => 'max:250|unique:stocks,code,NULL,id,deleted_at,NULL',
            //'code' => 'max:250|unique_custom_ignore_deleted:stocks,code,branch_id,' . Auth::user()->branch_id,
            'composition' => "max:200",
            'selling_price' => 'numeric|required',
            'product_type' => "nullable",
            'wholesale_price' => 'numeric|required',
            'cost_price' => 'numeric|required',

            'qty' => 'numeric|required',
            'item_discount' => 'numeric|required',
            'reorder_qty' => 'numeric|required',
            'description' => 'nullable',
            'show_img' => 'required',
            'department_id' => "nullable",
            'purchase_price' => 'numeric',
            'store_qty' => 'numeric',
            'mapping_value' => "numeric|min:1",
            'is_active' => "required",
            'menu_description' => 'nullable',
            'inventory_type' => 'required',
            'description' => 'nullable',
            'shelf_id' => "nullable",
            'supplier_id' => "nullable",
            'min_profit_margin' => 'numeric',
            'hs_code' => 'nullable'
        ];
        $customMessages = [
            'product_name.unique_custom_ignore_deleted' => 'The Product Name  Exist.',
            'code.unique_custom_ignore_deleted' => 'The Product Code  Exist.',

        ];
        $validator = Validator::make($data, $rules, $customMessages);
        if ($request->file != "null") {

            $upload_path = public_path('stock_img');

            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }
            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            Stock::create(
                ['code' => $product_code] +
                    $validator->validated() + ['image' => $generated_new_name, 'isSync' => '0'] +
                    Parent::user_branch_id()
            );
        } else {
            Stock::create([
                'code' => $product_code
            ] +
                $validator->validated() + $validator->validated() + ['isSync' => '0'] +
                Parent::user_branch_id());
        }

        // $res = Stock::orderBy('id', 'DESC')->get();
        return true;
    }

    public function updates(StockRequest $request)
    {
        $id = $request->id;
        Stock::where('id', $id)->update($request->validated());
        return true;
    }
    public function update(Request $request)
    {

        $data = json_decode($request['data'], true);
        if ($request->file != 'null') {
            $request->validate([
                'file' => 'mimes:jpeg,jpg,png,webp,gif|max:10000'
            ]);
        }

        $rules = [
            'id' => 'required',
            'name' => "required",
            'product_name' => 'required|unique:stocks,product_name,' . $data['id'] . ',id,deleted_at,NULL',
            //  'product_name' => 'required|max:200|edit_unique_custom_ignore_softdelete:stocks,product_name,branch_id,' . Auth::user()->branch_id . ',id,' . $data['id'],
            'unit_id' => "required",
            'product_category_id' => "required",
            //  'code' => 'required|max:250|edit_unique_custom_ignore_softdelete:stocks,code,branch_id,' . Auth::user()->branch_id . ',id,' . $data['id'],
            // 'code'=>'required|max:250|unique:stocks,code,NULL,id,deleted_at,NULL',
            'code' => 'required|unique:stocks,code,' . $data['id'] . ',id,deleted_at,NULL',
            'tax_dept_id' => "required",
             'item_discount' => 'numeric|required',
            'selling_price' => 'numeric|required',
            'product_type' => "nullable",
            'composition' => "max:200",
            'wholesale_price' => 'numeric|required',
            'cost_price' => 'numeric|required',

            'qty' => 'numeric|required',
            'reorder_qty' => 'numeric|required',
            'description' => 'nullable',
            'show_img' => 'required',

            'department_id' => "nullable",
            'purchase_price' => 'numeric',
            'store_qty' => 'numeric',
            'mapping_value' => "numeric|min:1",
            'is_active' => "required",
            'menu_description' => 'nullable',
            'inventory_type' => 'required',
            'description' => 'nullable',
            'shelf_id' => "nullable",
            'supplier_id' => "nullable",
            'min_profit_margin' => 'numeric',
            'hs_code' => 'nullable'

        ];
        $customMessages = [
            'product_name.edit_unique_custom_ignore_softdelete' => 'The Product Name  Exist.',
            'code.edit_unique_custom_ignore_softdelete' => 'The Product Code  Exist.',
        ];
        $validator = Validator::make($data, $rules, $customMessages);

        $stock = Stock::findOrFail($data['id']);
        if ($request->file != 'null') {

            $upload_path = public_path('stock_img');

            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }

            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            $this->deleteLogoImageFiles($data['id']);
            //   Stock::where('id', $data['id'])->update($validator->validated() + ['image' => $generated_new_name]);
            $stock->update($validator->validated() + ['image' => $generated_new_name]);
        } else {
            $stock->update($validator->validated());
            //  Stock::where('id', $data['id'])->update($validator->validated());
        }
        $res = Stock::where('id', $data['id'])->first();
        return response()->json($res);
    }
    public function deleteLogoImageFiles($id)
    {

        $item = Stock::where('id', $id)->first();

        $file = $item->image;
        if ($file) {


            $file_path = public_path() . '/stock_img/' . $file;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    public function deleteTrashedImageFiles($id)
    {

        $item = Stock::onlyTrashed()->where('id', $id)->first();

        $file = $item->image;
        if ($file) {


            $file_path = public_path() . '/stock_img/' . $file;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        // $this->deleteLogoImageFiles($id);
        Stock::find($id)->delete();
        return true;
    }

    public function delete_img(Request $request)
    {
        $id = $request->route('id');

        $this->deleteLogoImageFiles($id);
        Stock::where('id', $id)->update(['show_img' => 0, "image" => NULL]);
        return true;
    }
    public function fetch1()
    {
        $data = Stock::with(['product_category', 'unit', 'tax_dept', 'department', 'shelf', 'etims_item_type', 'etims_origin_country', 'etims_item_packaging_code', 'item_classification'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('inventory_type', 'LIKE', '%' . request('search') . '%')


                        ->orWhereHas('shelf', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('unit', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })->orderBy('id', 'DESC')->paginate(50);

        return response()->json($data);
    }
    function fetch()
    {
        $data = Stock::selectRaw('stocks.*, 
    CASE 
        WHEN selling_price > 0 
        THEN ROUND(((selling_price - purchase_price) / selling_price) * 100, 2)
        ELSE 0 
    END AS margin')->with(['product_category', 'unit', 'tax_dept', 'department', 'supplier', 'shelf', 'branchshelves.shelf'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {

                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('etims_code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('item_id', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('inventory_type', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('shelf', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('unit', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->when(request('sort_product'), function ($query) {
                $query->orderBy('product_name', request('sort_product'));
            })
            ->when(request('sort_code'), function ($query) {
                $query->orderBy('code', request('sort_code'));
            })
            ->when(request('sort_purchase'), function ($query) {
                $query->orderBy('purchase_price', request('sort_purchase'));
            })
            ->when(request('sort_selling'), function ($query) {
                $query->orderBy('selling_price', request('sort_selling'));
            })
            ->when(request('sort_settings_margin'), function ($query) {
                $query->orderBy('min_profit_margin', request('sort_settings_margin'));
            })
            ->when(request('sort_taxid'), function ($query) {
                $query->orderBy('item_id', request('sort_taxid'));
            })
            ->when(request('sort_margin'), function ($query) {
                $query->orderBy('margin', request('sort_margin'));
            })
            ->when(request('sort_category'), function ($query) {

                $query->join('product_categories', 'stocks.product_category_id', '=', 'product_categories.id')
                    ->select('stocks.*', 'product_categories.name as category_name_name')
                    ->orderBy('product_categories.name',  request('sort_category'));
            })
            ->when(request('sort_taxdept'), function ($query) {

                $query->join('tax_depts', 'stocks.tax_dept_id', '=', 'tax_depts.id')
                    ->select('stocks.*', 'tax_depts.tax_code as tax_code')
                    ->orderBy('tax_depts.tax_code',  request('sort_taxdept'));
            })

            ->when(request('sort_shelf'), function ($query) {

                $query->select('shelves.name as shelf_name', 'stock as s', 'branchshelves.*')
                    ->join('branchshelves AS bs', 'bs.stock_id', '=', 's.id')->join('branchshelves', 'branchshelves.shelf_id', '=', 'shelves.id')

                    ->orderBy('shelves.name',  request('sort_shelf'));
            })
            ->when(request('sort_unit'), function ($query) {

                $query->join('units', 'stocks.unit_id', '=', 'units.id')
                    ->select('stocks.*', 'units.name as unit_name,units.code AS unit_code')
                    ->orderBy('units.name', request('sort_order', request('sort_unit')));
            })->orderBy('id', 'DESC')

            ->paginate(50);

        return response()->json($data);
    }
    function search_items()
    {
        $data = Stock::with(['product_category', 'unit', 'tax_dept', 'department'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->skip(0)->take(10)->get();

        return response()->json($data);
    }
    function fetchPos()
    {
        $main_query = Stock::with(['product_category', 'unit', 'tax_dept', 'department', 'accompaniment.accompaniment'])
            ->when(request('category_id', '') != '', function ($query) {

                $query->where('product_category_id',  request('category_id'));
            })
            ->when(request('department_id', '') != '', function ($query) {

                $query->where('department_id',  request('department_id'));
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_type', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->where(["product_type" => "menu"]); //
        if (request('show_pos') == "show_pos") {
            $main_query->where('product_type', 'menu');
        }
        $data = $main_query->orderBy('id', 'DESC')->paginate(100);

        return response()->json($data);
    }
    public function fetchAll()
    {
        $data = Stock::with([
            'supplier',
            'product_category',
            'unit',
            'tax_dept',
            'department',
            'shelf',
            'etims_item_type',
            'etims_origin_country',
            'etims_item_packaging_code',
            'item_classification'
        ])->orderBy('id', 'DESC')->get();
        return response()->json($data);
    }

    function searchItem()
    {
        DB::enableQueryLog();
        $sortDirection = request('sort_shelf') === 'true' ? 'desc' : 'asc';

        $filled = array_filter(request()->only([
            'product_name',
            'code',
            'search_code',
            'search_description'
        ]));
        if (empty(request('search_code'))) {
            unset($filled['search_code']);
        }

        $main_query = Stock::with(['tax_dept', 'unit', 'shelf', 'branchshelves.shelf'])->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                if ($column == 'code') {
                    $query->where($column, $value);
                }
                if ($column == 'search_code') {
                    $query->where('code', 'LIKE', '%' . request('search_code') . '%');
                }
                if ($column == 'search_description') {
                    $query->where('description', 'LIKE', '%' . $value . '%');
                } else {
                    if (empty(request('search_code'))) {
                        $query->where($column, 'LIKE', '%' . $value . '%');
                    }
                }
            }
        });

        $data = $main_query->skip(0)->take(100)->get();
        /// print_r(DB::getQueryLog());
        return response()->json($data);
    }
    function searchPriceGroup()
    {
        DB::enableQueryLog();

        $sortDirection = request('sort_shelf') === 'true' ? 'desc' : 'asc';
        $price_group_id = request('price_group_id'); // Get price_group_id from the request

        $filled = array_filter(request()->only([
            'product_name',
            'code',
            'search_code',
            'search_description',
        ]));

        if (empty(request('search_code'))) {
            unset($filled['search_code']);
        }

        // Main query
        $main_query = Stock::with(['tax_dept', 'unit', 'shelf', 'branchshelves.shelf', 'etims_item_type', 'etims_origin_country', 'etims_item_packaging_code', 'item_classification'])
            ->select('stocks.*')
            ->when($price_group_id, function ($query) use ($price_group_id) {
                // Join with PriceGroupAmount table if price_group_id is provided
                $query->leftJoin('price_group_amounts', function ($join) use ($price_group_id) {
                    $join->on('stocks.id', '=', 'price_group_amounts.stock_id')
                        ->where('price_group_amounts.price_group_id', $price_group_id);
                });
                // Use COALESCE to determine which selling_price to select
                $query->addSelect(DB::raw("COALESCE(price_group_amounts.selling_price, stocks.selling_price) as selling_price"));
            }, function ($query) {
                // Default case: select selling_price from stocks table
                $query->addSelect('stocks.selling_price as selling_price');
            })
            ->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    if ($column == 'code') {
                        $query->where($column, $value);
                    } elseif ($column == 'search_code') {
                        $query->where('code', 'LIKE', '%' . request('search_code') . '%');
                    } elseif ($column == 'search_description') {
                        $query->where('description', 'LIKE', '%' . $value . '%');
                    } else {
                        if (empty(request('search_code'))) {
                            $query->where($column, 'LIKE', '%' . $value . '%');
                        }
                    }
                }
            });

        $data = $main_query->skip(0)->take(100)->get();

        // Log the query for debugging
        /// print_r(DB::getQueryLog());
        return response()->json($data);
    }
    function searchItemGeneric()
    {
        $main_query = Stock::with(['tax_dept', 'unit', 'shelf', 'branchshelves.shelf'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('product_name', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('item_id', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('product_category', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });

        if (request('code')) {
            $main_query = $main_query->where('code', request('code'));
        }

        $data = $main_query->skip(0)->take(100)->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(30);
        }
        /// print_r(DB::getQueryLog());
        return response()->json($data);
    }
    function searchShelfSortItemGeneric()
    {
        $main_query = Stock::with(['tax_dept', 'unit', 'branchshelves.shelf'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('item_id', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->when(request('sort_taxid'), function ($query) {
                $query->orderBy('item_id', request('sort_taxid'));
            })
            ->when(request('sort_code'), function ($query) {
                $query->orderBy('code', request('sort_code'));
            })
            ->when(request('sort_name'), function ($query) {
                $query->orderBy('name', request('sort_name'));
            });

        // Add sorting based on the related shelf name
        if (request('sort_shelf')  && in_array(request('sort_shelf'), ['ASC', 'DESC'])) {

            $main_query = $main_query->leftJoin('branchshelves', 'stocks.id', '=', 'branchshelves.stock_id')
                ->leftJoin('shelves', 'branchshelves.shelf_id', '=', 'shelves.id')
                ->orderBy('shelves.name', request('sort_shelf'))
                ->select('stocks.*'); // Ensure you select the primary table fields to avoid ambiguity
        }

        // Filtering by specific code
        if (request('code')) {
            $main_query = $main_query->where('code', request('code'));
        }

        // Handle pagination
        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data = $main_query->skip(0)->take(100)->get();
        }

        return response()->json($data);
    }


    function fetchStockTakeItems()
    {
        // Log incoming request parameters for debugging
        Log::info('Request Parameters:', request()->all());

        $main_query = Stock::query()
            ->select([
                'stocks.id as stock_id', // Primary key for stock record
                'stocks.code',
                'stocks.product_name',
                'stocks.id as item_id', // Map stocks.id to item_id for frontend
                DB::raw('COALESCE(shelves.name, "") as shelf'),
                DB::raw('0 as curr_qty'),
                DB::raw('1 as qty')
            ])
            ->leftJoin('branchshelves', 'stocks.id', '=', 'branchshelves.stock_id')
            ->leftJoin('shelves', 'branchshelves.shelf_id', '=', 'shelves.id')
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('stocks.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.id', 'LIKE', '%' . request('search') . '%') // Use id instead of item_id
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                    Log::info('Search Filter Applied:', ['search' => request('search')]);
                });
            })
            ->when(request('department_id'), function ($query) {
                $query->whereHas('inventory', function ($q) {
                    $q->where('id', request('department_id'));
                });
            })
            ->when(request('sort_taxid'), function ($query) {
                $query->orderBy('stocks.id', request('sort_taxid')); // Use id instead of item_id
                Log::info('Sort Tax ID Applied:', ['sort_taxid' => request('sort_taxid')]);
            })
            ->when(request('sort_code'), function ($query) {
                $query->orderBy('stocks.code', request('sort_code'));
                Log::info('Sort Code Applied:', ['sort_code' => request('sort_code')]);
            })
            ->when(request('sort_name'), function ($query) {
                $query->orderBy('stocks.name', request('sort_name'));
                Log::info('Sort Name Applied:', ['sort_name' => request('sort_name')]);
            })
            ->when(request('sort_shelf') && in_array(strtoupper(request('sort_shelf')), ['ASC', 'DESC']), function ($query) {
                $query->orderBy('shelves.name', strtoupper(request('sort_shelf')));
                Log::info('Sort Shelf Applied:', ['sort_shelf' => request('sort_shelf')]);
            })
            ->when(request('code'), function ($query) {
                $query->where('stocks.code', request('code'));
                Log::info('Code Filter Applied:', ['code' => request('code')]);
            });

        // Handle pagination
        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data = $main_query->take(100)->get();
        }

        // Log the result
        Log::info('Query Result:', ['count' => $data->count(), 'data' => $data->toArray()]);

        return response()->json($data);
    }
    function searchItempPriceGroup()
    {

        $filled = array_filter(request()->only([
            'product_name',
            'code',
            'item_id'
        ]));
        $data = Stock::with(['tax_dept', 'unit', 'shelf', 'branchshelves.shelf'])->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                if ($column == 'code') {
                    $query->where($column, $value);
                } else {

                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        })->skip(0)->take(20)->get();

        $priced_data = [];
        if (request('price_group_id')) {

            $priced_data = [];
            foreach ($data as  $value) {

                $check_price =  PriceGroupAmount::where([
                    'price_group_id' => request('price_group_id'),
                    'stock_id' => $value['id']
                ])->first();

                $selling_price = $value['selling_price'];
                if ($check_price) {

                    $selling_price = $check_price->selling_price;
                }
                $array1 = json_decode($value, true);

                $mearge = array_merge($array1, ['selling_price' => $selling_price]);
                $priced_data[] = $mearge;
            }
        } else {
            return response()->json($data);
        }
        return response()->json($priced_data);
    }
    public function stock_take(Request $request)
    {
        $this->validate($request, [
            'id' => "required",
            'type' => "required",
            'qty' => 'numeric|required',

        ]);
        DB::transaction(function () use ($request) {

            if ($request->type == "main_store") {
                Stock::where('id', $request->id)->increment('store_qty', $request->qty);
            }
            if ($request->type == "other_depts") {
                Stock::where('id', $request->id)->increment('qty', $request->qty);
            }

            StockTake::create([
                'stock_id' => $request->id,
                'type' => $request->type,
                'stock_take_type' => 'increment',
                'qty' => $request->qty,
            ] + Parent::user_branch_id());
        }, 5);
        return true;
    }
    public function deduct_stock(Request $request)
    {
        $this->validate($request, [
            'id' => "required",
            'type' => "required",
            'qty' => 'numeric|required',

        ]);
        if ($request->type == "main_store") {
            Stock::where('id', $request->id)->decrement('store_qty', $request->qty);
        }
        if ($request->type == "other_depts") {
            Stock::where('id', $request->id)->decrement('qty', $request->qty);
        }
        StockTake::create(['stock_id' => $request->id, 'type' => $request->type, 'stock_take_type' => 'decrement', 'qty' => $request->qty,] + Parent::user_branch_id());
        return true;
    }
    function fetchStockTake()
    {
        $main_query = StockTake::with(['stock', 'user']);
        $main_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        })->where(Parent::branch_array());
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->orderBy('id', 'DESC')->paginate(20);
        }
        //  print_r(DB::getQueryLog());
        return response()->json($res);
    }
    function productCode()
    {
        $latest = Stock::latest()->withTrashed()->first();
        if ($latest) {
            $productCode = ($latest->id + 1);

            $isUnique = Stock::where('code', $productCode)->withTrashed()->first();
            if ($isUnique) {

                $productCode = $this->uniqueCode($productCode);
            }
            return  $productCode;
        } else {
            return 1;
        }
    }
    function uniqueCode($code)
    {

        $productCode =  $code + 1;
        $isUnique = Stock::where('code', $productCode)->withTrashed()->first();
        if ($isUnique) {
            $productCode = $this->uniqueCode($productCode);
        }
        return $productCode;
    }

    function update_stock_cost(Request $request)
    {
        $this->validate($request, [
            'id' => "required",
            'total_cost' => 'numeric|required',

        ]);
        Stock::where(['id' => $request->id])->update([
            'purchase_price' => $request->total_cost,
            'composition' => "aggregate"
        ]);
        return true;
    }
    function bulkImageStatusUpdate(Request $request)
    {
        $status = $request->status;
        if ($status == "disable") {
            Stock::query()->update(['show_img' => '0']);
        } else if ($status == "enable") {
            Stock::query()->update(['show_img' => '1']);
        }
    }

    function fetchTrashed()
    {
        $data = Stock::onlyTrashed()->with(['product_category', 'unit', 'tax_dept', 'department'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC');
        $res = $data->get();
        if (request('page') > 0) {
            $res = $data->paginate(20);
        }
        return response()->json($res);
    }

    function restoreTrashed(Request $request)
    {


        Stock::onlyTrashed()->where('id', $request->stock_id)->update([
            'deleted_at' => null
        ]);
        return true;
    }

    function destroy_permanent(Request $request)
    {
        $id = $request->route('id');
        $this->deleteTrashedImageFiles($id);
        Stock::onlyTrashed()->where('id', $id)->forceDelete();
        return true;
    }
    public function deleteAllStock()
    {
        Stock::query()->delete();
        return true;
    }


    public function fetchStockSheet()
    {
        $main_query = Stock::select('stocks.id', 'stocks.product_name', 'stocks.description', 'units.name as unit', 'shelves.name as shelf', 'stocks.code', 'product_categories.name as category')
            ->whereNull('stocks.deleted_at')
            ->leftJoin('product_categories', function ($join) {
                $join->on('stocks.product_category_id', '=', 'product_categories.id')
                    ->whereNull('product_categories.deleted_at');
            })
            ->leftJoin('units', function ($join) {
                $join->on('stocks.unit_id', '=', 'units.id')
                    ->whereNull('units.deleted_at');
            })
            ->leftJoin('tax_depts', function ($join) {
                $join->on('stocks.tax_dept_id', '=', 'tax_depts.id')
                    ->whereNull('tax_depts.deleted_at');
            })
            ->leftJoin('branchshelves', function ($join) {
                $join->on('stocks.id', '=', 'branchshelves.stock_id')
                    ->whereNull('branchshelves.deleted_at')
                    ->where('branchshelves.branch_id', Auth::user()->branch_id);
            })
            ->leftJoin('shelves', function ($join) {
                $join->on('branchshelves.shelf_id', '=', 'shelves.id')
                    ->whereNull('shelves.deleted_at')
                    ->where('shelves.branch_id', Auth::user()->branch_id);
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('stocks.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.product_type', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('stocks.composition', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('product_categories.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('units.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('shelves.name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->when(request('product_category_id'), function ($query) {
                $ids = is_array(request('product_category_id'))
                    ? request('product_category_id')
                    : explode(',', request('product_category_id'));
                $query->whereIn('stocks.product_category_id', $ids);
            })
            ->when(request('sort_category'), function ($query) {
                $query->orderBy('product_categories.name', request('sort_category'));
            })
            ->when(request('sort_code'), function ($query) {
                $query->orderBy('stocks.code', request('sort_code'));
            })
            ->when(request('sort_name'), function ($query) {
                $query->orderBy('stocks.name', request('sort_name'));
            })
            ->when(request('sort_shelf'), function ($query) {
                $query->orderBy('shelves.name', request('sort_shelf'));
            })
            ->when(request('shelf_id'), function ($query) {
                $shelfIds = is_array(request('shelf_id'))
                    ? request('shelf_id')
                    : explode(',', request('shelf_id'));
                $query->whereIn('branchshelves.shelf_id', $shelfIds);
            })
            ->orderBy('stocks.id', 'DESC');



        // Fetch data with pagination
        $data = request('page') > 0 ? $main_query->paginate(50) : $main_query->get();


        return response()->json($data);
    }
}
