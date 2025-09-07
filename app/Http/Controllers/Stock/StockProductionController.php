<?php

namespace App\Http\Controllers\Stock;

use App\Disassembly\StockDisassembly;
use Carbon\Carbon;
use App\Stock\Stock;
use App\Stock\IssueStock;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Ingredient\Ingredient;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidateInventoryQty;
use App\Http\Controllers\Controller;
use App\Rules\CheckStockAvailablity;
use App\ProductionCost\ProductionCost;
use App\Http\Controllers\BaseController;
use App\Ingredient\IngredientProduction;
use App\Ingredient\ProductionCostDetails;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckStoreStockAvailability;
use App\Rules\ValidateProductionMaterials;
use App\Rules\ValidateRawMaterialAvailabilityInProduction;
use App\Rules\ValidateParentStockAvailabilityDisassemblyProduct;

class StockProductionController extends BaseController
{
    function fetchRawMaterials(Request $request)
    {
        $request->validate([
            'stock_id' => 'required',
            'desired_qty' => 'required|numeric|gt:0',

        ], ['stock_id.required' => "Select production item"]);
        $this->validate($request, [
            'stock_id' => new ValidateProductionMaterials(),

        ]);
        $res_ingredient = Ingredient::with(['ingredient.unit'])->where('stock_id', $request->stock_id)->get();
        $res_pr_cost = ProductionCost::where('stock_id', $request->stock_id)->get();

        return response()->json(
            array(
                'raw_materials' => $res_ingredient,
                'production_data' => $res_pr_cost
            )
        );
    }

    function completeProductIssue(Request $request)
    {


        //var_dump($request->all());


        $validator = Validator::validate($request->all(), [
            'raw_materials' => 'required|array',
            'raw_materials.*.qty' => 'required|numeric|gt:0',
            "production_cost_data" => 'array',
            'production_cost_data.*.total_amount' => 'numeric|required|gt:0',
            'raw_materials.*.stock_id' => [new ValidateRawMaterialAvailabilityInProduction($request)],
            'department_id' => "required",
            "raw_material_department_id" => "required",
            'issue_no' => "required",
            'production_no' => 'required'
        ]);
        DB::transaction(function () use ($request) {

            for ($i = 0; $i < count($request->raw_materials); $i++) {
                $material_stock_id = $request->raw_materials[$i]['stock_id'];

                $raw_materials = [
                    'stock_id' => $request->stock_id,
                    'user_id' => Parent::user_id(),
                    'branch_id' => Parent::branch_id(),
                    'material_id' =>  $material_stock_id,
                    'material_qty' => $request->raw_materials[$i]['qty'],
                    'material_cost' => $request->raw_materials[$i]['cost_price'],
                    'desired_qty' => $request->desired_qty,
                    'cost_price' => $request->cost_price,
                    'total_materials_cost' => ($request->raw_materials[$i]['cost_price'] * $request->raw_materials[$i]['qty']),
                    'department_id' => $request->department_id,
                    'issue_no' => $request->issue_no,
                    'production_no' => $request->production_no,
                    'end_product' => $request->end_product
                ];
                IngredientProduction::create($raw_materials);


                $prev_store_qty = 0;


                $purchase_price = Stock::where('id', $material_stock_id)->first()->purchase_price;

                IssueStock::create(
                    [
                        'stock_id' => $material_stock_id,
                        'branch_id' => Parent::branch_id(),
                        'unit_id' => $request->raw_materials[$i]['unit_id'],
                        'department_id' => $request->department_id,
                        'source_department_id' => $request->raw_material_department_id,
                        'issue_no' => $request->issue_no,
                        'user_id' => Parent::user_id(),
                        "issued_staff_id" => Parent::user_id(),
                        'qty_issued' => $request->raw_materials[$i]['qty'],
                        'mapping_value' => 1,
                        'final_qty' => $request->raw_materials[$i]['qty'],
                        'unit' => $request->raw_materials[$i]['unit'],
                        'date_issued' => Parent::currentDate(),
                        'purchase_price' => $purchase_price,
                        'row_total' => ($purchase_price * $request->raw_materials[$i]['qty']),
                        'opening_stock' => $prev_store_qty,
                        'status' => "complete",


                    ]
                );



                Stock::where(['id' => $material_stock_id])->decrement('store_qty',  $request->raw_materials[$i]['qty']);
                if (Parent::isInventory($material_stock_id)) {
                    Inventory::updateOrCreate(
                        [
                            'stock_id' =>  $material_stock_id,
                            'department_id' =>  $request->raw_material_department_id,
                            'branch_id' => Parent::branch_id()
                        ],
                        ['qty' => DB::raw('qty -' .  $request->raw_materials[$i]['qty'])]
                    );
                }
            }

            if ($request->completeProduction == 'complete') {
                IngredientProduction::where(['production_no' => $request->production_no])
                    ->update(['production_status' => 'completed', 'production_date' => Parent::currentDateTime()]);
                ProductionCostDetails::where(['production_no' => $request->production_no])
                    ->update(['production_status' => 'completed']);
                //  if($request->end_product){
                //  Stock::where(['id' => $request->stock_id])->increment('qty', $request->desired_qty);
                //   }else{
                if (Parent::isInventory($request->stock_id)) {
                    Inventory::updateOrCreate(
                        [
                            'stock_id' =>  $request->stock_id,
                            'department_id' =>  $request->department_id,
                            'branch_id' => Parent::branch_id()
                        ],
                        ['qty' => DB::raw('qty +' .  $request->desired_qty)]
                    );
                }

                //   }

            }


            if (count($request->production_cost_data)) {
                for ($i = 0; $i < count($request->production_cost_data); $i++) {
                    $production_cost_data = [
                        'stock_id' => $request->stock_id,
                        'user_id' => Parent::user_id(),
                        'total_cost' =>  $request->production_cost_data[$i]['total_amount'],
                        'cost_price' => $request->production_cost,
                        'details' => $request->production_cost_data[$i]['details'],
                        'department_id' => $request->department_id,
                        'issue_no' => $request->issue_no,
                        'production_no' => $request->production_no
                    ];
                    ProductionCostDetails::create($production_cost_data);
                }
            }
        }, 5);
    }

    function fetchIssueNo()
    {

        return response()->json($this->issueNumber());
    }
    function productionNo()
    {
        $latest = IngredientProduction::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'PDT' . sprintf('%04d', $string);
        $isUnique = IngredientProduction::where('production_no', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'PDT' . ($latest->id + 1);
        }
        return response()->json($req_no);
    }
    function issueNumber()
    {

        $latest = IssueStock::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'ISN' . sprintf('%04d', $string);
        $isUnique = IssueStock::where('issue_no', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'ISN' . ($latest->id + 1);
        }
        return  $req_no;
    }

    function fetch()
    {

        $raw_query = IngredientProduction::with(['stock', 'user']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('production_date', [request('from'),  request('to')]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhere('production_no', 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->selectRaw('*,SUM(total_materials_cost) AS sum_total_materials_cost,
        SUM(material_qty) AS sum_material_qty')->groupBy('production_no')->latest('created_at');

        if (request('is_complete') == 'true') {
            $raw_query->where('production_status', 'progress');
        }
        $row = $raw_query->get();
        if (request('page') > 0) {
            $row = $raw_query->paginate(20);
        }
        return response()->json($row);
    }

    function fetchExcel()
    {
        $raw_query = IngredientProduction::with(['stock', 'user', 'material']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('production_date', [request('from'),  request('to')]);
        });
        $raw_query->latest('created_at');

        if (request('is_complete') == 'true') {
            $raw_query->where('production_status', 'progress');
        }
        $row = $raw_query->get();
        if (request('page') > 0) {
            $row = $raw_query->paginate(20);
        }
        return response()->json($row);
    }
    function fetchTotal()
    {

        $raw_query = IngredientProduction::when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('production_date', [request('from'),  request('to')]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhere('production_no', 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->selectRaw('SUM(total_materials_cost) AS sum_total_materials_cost,
        SUM(material_qty) AS sum_material_qty,SUM(desired_qty*cost_price) AS sum_total_produced,SUM(desired_qty) AS sum_qty')->latest('created_at');

        if (request('is_complete') == 'true') {
            $raw_query->where('production_status', 'progress');
        }
        $row = $raw_query->first();
        if (request('page') > 0) {
            $row = $raw_query->paginate(30);
        }
        return response()->json($row);
    }
    function fetchTotalQtyProduced()
    {


        DB::enableQueryLog();
        $raw_query = IngredientProduction::when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('production_date', [request('from'),  request('to')]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhere('production_no', 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->select(DB::raw('(desired_qty AS sum_qty_produced'))->groupBy('production_no');

        if (request('is_complete') == 'true') {
            //$raw_query->where('production_status', 'progress');
        }
        $row = $raw_query->first();
        // print_r(DB::getQueryLog());
        return response()->json($row);
    }

    function completeProduction(Request $request)
    {

        DB::transaction(function () use ($request) {
            IngredientProduction::where(['production_no' => $request->production_no])
                ->update(['production_status' => 'completed', 'production_date' => Parent::currentDateTime()]);
            ProductionCostDetails::where(['production_no' => $request->production_no])->update(['production_status' => 'completed']);
            // Stock::where(['id' => $request->stock_id])->increment('qty', $request->desired_qty);
            //  if($request->end_product){
            //  Stock::where(['id' => $request->stock_id])->increment('qty', $request->desired_qty);


            //  }else{
            Stock::where(['id' => $request->stock_id])->increment('store_qty', $request->desired_qty);
            Inventory::updateOrCreate(
                [
                    'stock_id' =>  $request->stock_id,
                    'department_id' =>  $request->department_id,
                    'branch_id' => Parent::branch_id()
                ],
                ['qty' => DB::raw('qty +' .  $request->desired_qty)]
            );
            // }

        }, 5);
        return true;
    }

    function fetchOngoingProductionMaterials()
    {
        $res =   IngredientProduction::with(['stock', 'material.unit'])->where(['production_no' => request('production_no')])->get();
        return response()->json($res);
    }

    function updateRawMterials(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'raw_material_department_id' => 'required',
            'issue_no' => "required",
            'production_no' => 'required',
            'cost_price' => 'required|numeric|gte:0',
            'unit_id' => 'required',
            'unit' => 'required',
            'material_id' => [new ValidateInventoryQty($request)],
        ]);
        DB::transaction(function () use ($request) {
            $raw_materials = [
                'stock_id' => $request->stock_id,
                'user_id' => Parent::user_id(),
                'branch_id' => Parent::branch_id(),
                'material_id' =>  $request->material_id,
                'material_qty' => $request->material_qty,
                'material_cost' => $request->material_cost,
                'desired_qty' => $request->desired_qty,
                'cost_price' => $request->cost_price,
                'total_materials_cost' => ($request->material_cost * $request->material_qty),
                'department_id' => $request->department_id,
                'issue_no' => $request->issue_no,
                'production_no' => $request->production_no
            ];
            IngredientProduction::updateOrCreate(
                ['material_id' =>  $request->material_id, 'issue_no' => $request->issue_no, 'production_no' => $request->production_no],

                [
                    "material_qty" => DB::raw('material_qty +' . $request->material_qty),
                    "total_materials_cost" => DB::raw('total_materials_cost +' . ($request->material_cost * $request->material_qty))
                ] + $raw_materials
            );

            Stock::where(['id' => $request->material_id])->decrement('store_qty',  $request->material_qty);
            $purchase_price = Stock::where('id', $request->material_id)->first()->purchase_price;
            if (Parent::isInventory($request->material_id)) {
                Inventory::updateOrCreate(
                    [
                        'stock_id' =>  $request->material_id,
                        'department_id' =>  $request->raw_material_department_id,
                        'branch_id' => Parent::branch_id()
                    ],
                    ['qty' => DB::raw('qty -' .  $request->material_qty)]
                );
            }
            IssueStock::create(
                [
                    'stock_id' => $request->material_id,
                    'branch_id' => Parent::branch_id(),
                    'unit_id' => $request->unit_id,
                    'department_id' => $request->department_id,
                    'source_department_id' => $request->raw_material_department_id,
                    'issue_no' => $request->issue_no,
                    'user_id' => Parent::user_id(),
                    'qty_issued' => $request->material_qty,
                    'mapping_value' => 1,
                    'final_qty' => $request->material_qty,
                    'unit' => $request->unit,
                    'date_issued' => Parent::currentDate(),
                    'purchase_price' => $purchase_price,
                    'row_total' => ($purchase_price * $request->material_qty),
                    'opening_stock' => 0,
                    'status' => "complete",
                    'issued_staff_id' => Parent::user_id()

                ]
            );
        }, 5);
        $res =   IngredientProduction::with(['stock', 'material.unit'])
            ->where(['production_no' => $request->production_no])->get();
        return response()->json($res);
    }

    function productionDetails()
    {
        $res =   IngredientProduction::with(['stock', 'material.unit'])->where(['production_no' => request('production_no')])->get();
        $res1 =  ProductionCostDetails::where(['production_no' => request('production_no')])->get();
        return response()->json(['production_details' => $res1, 'materials' => $res]);
    }
}
