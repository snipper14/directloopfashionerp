<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Stock\IssueStock;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Disassembly\StockDisassembly;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateParentStockAvailabilityDisassemblyProduct;

class StockDisassemblyController extends BaseController
{
    function productDisassembly(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'child_elements_arr' => 'required|array',
            'child_elements_arr.*.qty' => 'required|numeric|gt:0',
            'stock_id' => [new ValidateParentStockAvailabilityDisassemblyProduct($request)],
            'parent_department_id' => "required",
            "child_department_id" => "required",
            'issue_no' => "required",
            'production_no' => 'required'
        ], [
            'parent_department_id.required' => 'Select parent location',
            'child_department_id.required' => 'Select child location'
        ]);
        DB::transaction(function () use ($request) {

            for ($i = 0; $i < count($request->child_elements_arr); $i++) {
                $child_stock_id = $request->child_elements_arr[$i]['child_stock_id'];

                $raw_materials = [
                    'stock_id' => $request->stock_id,
                    'user_id' => Parent::user_id(),
                    'branch_id' => Parent::branch_id(),
                    'child_stock_id' =>  $child_stock_id,
                    'child_qty' => $request->child_elements_arr[$i]['qty'],
                    'child_cost' => $request->child_elements_arr[$i]['cost_price'],
                    'child_totalcost' => ($request->child_elements_arr[$i]['cost_price'] * $request->child_elements_arr[$i]['qty']),
                    'parent_qty' => $request->parent_qty,
                    'parent_cost_price' => $request->cost_price,
                    'parent_totalcost' => ($request->parent_qty * $request->cost_price),
                    'parent_department_id' => $request->parent_department_id,
                    'child_department_id' => $request->child_department_id,
                    'issue_no' => $request->issue_no,
                    'production_no' => $request->production_no,
                    'disassembly_date' => $request->disassembly_date
                ];
                StockDisassembly::create($raw_materials);


                $prev_store_qty = 0;


                $purchase_price = Stock::where('id', $child_stock_id)->first()->purchase_price;

                IssueStock::create(
                    [
                        'stock_id' => $child_stock_id,
                        'branch_id' => Parent::branch_id(),
                        'unit_id' => $request->child_elements_arr[$i]['unit_id'],
                        'department_id' => $request->child_department_id,
                        'source_department_id' => $request->parent_department_id,
                        'issue_no' => $request->issue_no,
                        'user_id' => Parent::user_id(),
                        "issued_staff_id" => Parent::user_id(),
                        'qty_issued' => $request->child_elements_arr[$i]['qty'],
                        'mapping_value' => 1,
                        'final_qty' => $request->child_elements_arr[$i]['qty'],
                        'unit' => $request->child_elements_arr[$i]['unit'],
                        'date_issued' => $request->disassembly_date,
                        'purchase_price' => $purchase_price,
                        'row_total' => ($purchase_price * $request->child_elements_arr[$i]['qty']),
                        'opening_stock' => $prev_store_qty,
                        'status' => "complete",


                    ]
                );





                Inventory::updateOrCreate(
                    [
                        'stock_id' =>  $child_stock_id,
                        'department_id' =>  $request->child_department_id,
                        'branch_id' => Parent::branch_id()
                    ],
                    ['qty' => DB::raw('qty +' .  $request->child_elements_arr[$i]['qty'])]
                );
            }
        }, 5);
        Inventory::updateOrCreate(
            [
                'stock_id' =>  $request->stock_id,
                'department_id' =>  $request->parent_department_id,
                'branch_id' => Parent::branch_id()
            ],
            ['qty' => DB::raw('qty -' .  $request->parent_qty)]
        );
    }

    function fetch()
    {
        $raw_query = StockDisassembly::with(['stock', 'user', 'child_stock', 'child_department', 'parent_department']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('disassembly_date', [request('from'),  request('to')]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhere('production_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('issue_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('production_no', 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->selectRaw('*,SUM(child_totalcost) AS sum_child_totalcost,
        SUM(child_qty) AS sum_child_qty')->groupBy('production_no')->latest('created_at');


        $row = $raw_query->get();
        if (request('page') > 0) {
            $row = $raw_query->paginate(20);
        }
        return response()->json($row);
    }
    function fetchDetails(){
        $raw_query = StockDisassembly::with(['stock', 'user', 'child_stock', 'child_department', 'parent_department']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {

            $query->whereBetween('disassembly_date', [request('from'),  request('to')]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%');
            });
            $query->orWhereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhere('production_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('issue_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('production_no', 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->where('production_no',request('production_no'))->latest('created_at');


        $row = $raw_query->get();
        if (request('page') > 0) {
            $row = $raw_query->paginate(20);
        }
        return response()->json($row);
    }
}
