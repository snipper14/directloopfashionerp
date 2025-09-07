<?php

namespace App\Http\Controllers\EndPoint;

use App\Category\ProductCategory;
use App\Stock\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Unit\Unit;

class StockSyncController extends BaseController
{

    function fetchNewStock()
    {
        $res = Stock::withoutGlobalScope(BranchScope::class)->where(['isSync' => '0'])->get();
        return response()->json($res);
    }


    function updateNewStockLocally(Request $request)
    {

        $items = array();
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $data = $request->issue_data;
            foreach ($request->issue_data as  $value) {
                //  if ($this->issueIfExist($value['issue_no'], $value['stock_id'])) {
                Stock::insert(
                    [
                        'id'=>$value['id'],
                        'branch_id' => $value['branch_id'],
                        'user_id' => $value['user_id'],
                        'product_category_id' => $value['product_category_id'],
                        'unit_id' => $value['unit_id'],
                        'tax_dept_id' => $value['tax_dept_id'],
                        'product_name' => $value['product_name'],
                        'name' => $value['name'],
                        'product_type' => $value['product_type'],
                        'code' => $value['code'],
                        'qty' => $value['qty'],
                        'reorder_qty' => $value['reorder_qty'],
                        'selling_price' => $value['selling_price'],
                        'wholesale_price' => $value['wholesale_price'],
                        'cost_price' => $value['cost_price'],
                        'production_cost' => $value['production_cost'],

                        'description' => $value['description'],
                        'image' => $value['image'],
                        'show_img' => $value['show_img'],
                        'composition' => $value['composition'],

                        'isKitchenProduct' => $value['isKitchenProduct'],
                        'isDrinkProduct' => $value['isDrinkProduct'],
                        'department_id' => $value['department_id'],
                        'purchase_price' => $value['purchase_price'],

                        'store_qty' => $value['store_qty'],
                        'main_store_qty' => $value['main_store_qty'],
                        'mapping_value' => $value['mapping_value'],
                        'is_active' => $value['is_active'],

                        'menu_description' => $value['menu_description'],
                        'isSync' => '1',
                        'created_at'=>$value['created_at'],
                        'updated_at'=>$value['updated_at']

                    ]
                );
            }
            //   }
        }, 5);

        return response()->json(["status" => 'success']);
    }

    function updateStockSyncStatus()
    {
        Stock::withoutGlobalScope(BranchScope::class)->where(['isSync' => '0'])->update(['isSync' => '1']);
        return response()->json(["status" => 'success']);
    }




    function fetchNewCategory()
    {
        $res = ProductCategory::where(['isSync' => '0'])->get();
        return response()->json($res);
    }


    function updateNewCategoriesLocally(Request $request)
    {

        $items = array();
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {

            foreach ($request->category_data as  $value) {
                //  if ($this->issueIfExist($value['issue_no'], $value['stock_id'])) {
                ProductCategory::insert(
                    [
                        'id'=>$value['id'],
                        'name' => $value['name'],
                        'department_id' => $value['department_id'],
                        'banner' => $value['banner'],
                        'details' => $value['details'],
                        'show_pos' => $value['show_pos'],
                        'isSync' => '1',
                        'created_at'=>$value['created_at'],
                        'updated_at'=>$value['updated_at']


                    ]
                );
            }
            //   }
        }, 5);

        return response()->json(["status" => 'success']);
    }

    function updateCategorySyncStatus()
    {
        ProductCategory::where(['isSync' => '0'])->update(['isSync' => '1']);
        return response()->json(["status" => 'success']);
    }













    function fetchNewUnit()
    {
        $res = Unit::where(['isSync' => '0'])->get();
        return response()->json($res);
    }


    function updateNewUnitLocally(Request $request)
    {

        $items = array();
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {

            foreach ($request->unit_data as  $value) {
                //  if ($this->issueIfExist($value['issue_no'], $value['stock_id'])) {
                Unit::insert(
                    [
                        'id'=>$value['id'],
                        'name' => $value['name'],
                        'branch_id' => $value['branch_id'],
                        'user_id' => $value['user_id'],

                        'isSync' => '1',
                        'created_at'=>$value['created_at'],
                        'updated_at'=>$value['updated_at']


                    ]
                );
            }
            //   }
        }, 5);

        return response()->json(["status" => 'success']);
    }

    function updateUnitSyncStatus()
    {
        Unit::where(['isSync' => '0'])->update(['isSync' => '1']);
        return response()->json(["status" => 'success']);
    }
}
