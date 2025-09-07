<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Stock\StockMovt;
use App\Stock\DeductStock;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Stock\AddStock;

class StockUpdateController extends BaseController
{
    function create(Request $request)
    {
        $this->validate($request, [
            'id' => "required",
            'type' => "required",
            'qty' => 'numeric|required',

        ]);
        DB::transaction(function () use ($request) {
            $current_store_qty = Stock::where('id', $request->id)->first()->store_qty;
            StockMovt::create([
                "opening_stock" => $current_store_qty,
                'physical_qty' => $request->qty,
                'stock_id' => $request->id,
                'date_recorded' => Parent::currentDate(),
                'system_qty' => $request->qty,
            ] + Parent::user_branch_id());

            if ($request->type == "main_store") {
                Stock::where('id', $request->id)->update(['store_qty' => $request->qty]);
            }
            if ($request->type == "other_depts") {
                Stock::where('id', $request->id)->update(['qty' => $request->qty]);
            }

            PhysicalStock::create(["opening_stock" => $current_store_qty, 'stock_id' => $request->id, 'type' => $request->type, 'qty' => $request->qty,] + Parent::user_branch_id());
        }, 5);
        return true;
    }

    function fetch()
    {
         $from_time = request('from_time') ? date(request('from_time')) : null;
        $to_time = request('to_time') ? date(request('to_time')) : null;
        $main_query = PhysicalStock::with([
            'stock:id,product_name,code', // Include specific columns from `stock` table
            'user:id,name', // Include specific columns from `user` table
            'department:id,department' // Include specific columns from `department` table
        ])
            ->select(
                'created_at',
                'id',
                'user_id',
                'branch_id',
                'department_id',
                'opening_stock',
                'qty',
                'type',
                'stock_id',
                'original_qty',
                'selling_price',
                'purchase_price',
                DB::raw("
            CASE 
                WHEN type = 'increment' THEN (qty - original_qty) 
                WHEN type = 'decrement' THEN -(qty - original_qty) 
                ELSE (qty - original_qty) 
            END as variation_qty
        "),
                DB::raw("
            CASE 
                WHEN type = 'increment' THEN (qty - original_qty) * purchase_price
                WHEN type = 'decrement' THEN -(qty - original_qty) * purchase_price
                ELSE (qty - original_qty) * purchase_price
            END as variation_purchase_price
        "),
                DB::raw("
            CASE 
                WHEN type = 'increment' THEN (qty - original_qty) * selling_price
                WHEN type = 'decrement' THEN -(qty - original_qty) * selling_price
                ELSE (qty - original_qty) * selling_price
            END as variation_selling_price
        ")
            )
            ->where(Parent::branch_array()) // Add branch filtering if needed
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                })
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('department', function ($q) {
                        $q->where('department', 'LIKE', '%' . request('search') . '%');
                    });
            });
              $main_query->when($from_time && $to_time, function ($query) use ($from_time, $to_time) {
            $query->whereBetween('created_at', [$from_time, $to_time]);
        })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            })
            ->when(request('sort_variation_qty'), function ($query) {
                $query->orderBy('variation_qty', request('sort_variation_qty'));
            })
            ->when(request('sort_purchase_price'), function ($query) {
                $query->orderBy('purchase_price', request('sort_purchase_price'));
            })
            ->when(request('sort_selling_price'), function ($query) {
                $query->orderBy('selling_price', request('sort_selling_price'));
            })
            ->when(request('sort_variation_purchase_price'), function ($query) {
                $query->orderBy('variation_purchase_price', request('sort_variation_purchase_price'));
            })
            ->when(request('sort_variation_selling_price'), function ($query) {
                $query->orderBy('variation_selling_price', request('sort_variation_selling_price'));
            })

            ->orderBy('id', 'DESC');

        // Fetch results
        $results = request('page', 0) > 0
            ? $main_query->paginate(20)
            : $main_query->get();

        return response()->json($results);
    }
    function fetchPrev()
    {
        $main_query = PhysicalStock::with(['stock', 'user', 'department']);
        $main_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%');
            })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })->orWhereHas('department', function ($q) {
                    $q->where('department', 'LIKE', '%' . request('search') . '%');
                });
        })->where(Parent::branch_array());
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->orderBy('id', 'DESC')->paginate(20);
        }
        //  print_r(DB::getQueryLog());
        return response()->json($res);
    }
    function fetchHistory()
    {
        $main_query = PhysicalStock::with(['stock', 'user', 'department']);
        $main_query->when(request('search', '') != '', function ($query) {
            $query->whereHas('stock', function ($q) {
                $q->where('product_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%');
            })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })->orWhereHas('department', function ($q) {
                    $q->where('department', 'LIKE', '%' . request('search') . '%');
                });
        })->where(Parent::branch_array());
        $res = $main_query->orderBy('id', 'DESC')->first();

        return response()->json($res);
    }

    function fetchUpdateByTimesmtamp()
    {

        $res = PhysicalStock::with(["stock", "user"])->where([
            "created_at" => request("created_at"),
            "stock_id" => request("stock_id"),
            'type' => 'Manual Update'
        ])->get();
        return $res;
    }
    function fetchDecrementByTimesmtamp()
    {
        $res =  DeductStock::with(["stock", "user"])->where("unique_code", request("ref"))->get();
        return $res;
    }

    function fetchIncrementByTimesmtamp(){
        $res =  AddStock::with(["stock", "user"])->where("entry_code", request("ref"))->get();
        return $res;
    }
}
