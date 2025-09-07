<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Stock\MergeStock;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class MergeStockController extends BaseController
{
    function create(Request $request)
    {

        $validatedData = $request->validate([
            'stock_id' => 'required|exists:stocks,id', // Ensure stock_id exists in the stocks table
            'deleted_stock_id' => 'required|exists:stocks,id', // Ensure deleted_stock_id exists in the stocks table


        ]);
        $request->validate([
            'deleted_stock_id' => 'different:stock_id',
        ]);
        DB::transaction(function () use ($request, $validatedData) {
            // Insert data into the database
            $branches = Inventory::withoutGlobalScope(BranchScope::class)->groupBy('branch_id')->get();
            $inventory_stock = Inventory::withoutGlobalScope(BranchScope::class)->where(['stock_id' => $request->stock_id])->first();
            $original_qty = $inventory_stock ? $inventory_stock->qty : 0;
            foreach ($branches as  $value) {

                //Inventory to delete
                $deleted_inventory = Inventory::withoutGlobalScope(BranchScope::class)->where(
                    ['stock_id' => $request->deleted_stock_id, 'branch_id' => $value['branch_id']]
                )->selectRaw('SUM(qty) AS sum_qty')->groupBy('stock_id')->first();
                
                $total_deleted_qty = $deleted_inventory ? $deleted_inventory->sum_qty : 0;
                if ($deleted_inventory) {
                    //if there is stock in deleted items
                    $department = Department::withoutGlobalScope(BranchScope::class)->where(['show_pos' => 1, 'branch_id' => $value['branch_id']])->first();
                    $department_2 = Department::withoutGlobalScope(BranchScope::class)->where(['branch_id' => $value['branch_id']])->first();
                    $department_id = $department ? $department->id : $department_2->id;
                    Inventory::withoutGlobalScope(BranchScope::class)->updateOrCreate(
                        ['department_id' => $department_id, 'stock_id' => $request->stock_id],
                        [
                            'qty' => DB::raw('qty +' . $total_deleted_qty),

                            'branch_id' => $value['branch_id']
                        ]
                    );
                    $stock = Stock::where('id', $request->stock_id)->first();
                    // PhysicalStock::withoutGlobalScope(BranchScope::class)->create([
                    //     'department_id' => $department_id,
                    //     'qty' => $total_deleted_qty,
                    //     'stock_id' => $request->stock_id,
                    //     'type' => "Manual Update",
                    //     'original_qty' => $original_qty,
                    //     'selling_price' => $stock->selling_price,
                    //     'purchase_price' => $stock->purchase_price,
                    //     'branch_id' => $value['branch_id'],

                    //     'user_id' => Parent::user_id()
                    // ]);
                }
                MergeStock::withoutGlobalScope(BranchScope::class)->create($validatedData + ['qty_merged' => $total_deleted_qty, 'user_id' => Parent::user_id(), 'branch_id' => $value['branch_id']]);
              
            }
            Inventory::withoutGlobalScope(BranchScope::class)->where(
                ['stock_id' => $request->deleted_stock_id]
            )->delete();
            Stock::where('id', $request->deleted_stock_id)->delete();
        }, 5);

        // Insert data into the database

    }

    function fetch()
    {
        $main_query =  MergeStock::with(['stock', 'deletedStock', 'user'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('deletedStock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%');
                })
                    ->orWhereHas('deletedStock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });
          if(request("stock_id")){
            $main_query->where("stock_id",request("stock_id"));
        }
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->orderBy('id', 'DESC')->paginate(40);
        }
      
        //  print_r(DB::getQueryLog());
        return response()->json($res);
    }
}
