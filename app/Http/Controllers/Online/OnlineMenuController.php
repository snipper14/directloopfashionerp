<?php

namespace App\Http\Controllers\Online;

use Illuminate\Http\Request;
use App\Category\ProductCategory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Stock\Stock;

class OnlineMenuController extends BaseController
{
    public function fetch()
    {
        $data = ProductCategory::with(['department'])->where('show_pos', 1)->get();
        $menu = array();
        foreach ($data as $value) {
            $product_data = Stock::with(['unit','product_category','accompaniment.accompaniment'])->where(['product_category_id' => $value['id']])->get();
            $menu[] = array(
                'category_id'=>$value['id'],
                'category' => $value['name'],
                'category_img'=>$value['img_url'],
                "product_data"=>$product_data
            );
        }
        return response()->json($menu);
    }
    function fetchStock()
    {
        $main_query = Stock::with(['unit', 'product_category','accompaniment.stock'])->where(['product_type' => 'menu']);
        $res = $main_query->inRandomOrder()->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(15);
        }

        return response()->json($res);
    }
}
