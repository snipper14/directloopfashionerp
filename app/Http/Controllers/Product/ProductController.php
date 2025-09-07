<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PayrollProduct\PayrollProduct;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Product\PayrollProductRequest;

class ProductController extends BaseController
{
    public function create(PayrollProductRequest $request)
    {
          $data = PayrollProduct::create(
              ['product_code'=>$this->productCode()]
              +$request->validated()+Parent::user_branch_id());
          return response()->json(['results' => $data]);
    }

    public function update(PayrollProductRequest $request)
    {
          $data = PayrollProduct::where('id', $request->id)->update($request->validated());
          return response()->json(['results' => $data]);
    }
    public function fetch()
    {
          $filled = array_filter(request()->only([
                'name',
                'description',
                'product_code',
          ]));
          $raw_query = PayrollProduct::when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                      $query->where($column, 'LIKE', '%' . $value . '%');
                }
          })
                ->when(request('search', '') != '', function ($query) {
                      $query->where(function ($q) {
                            $q->where('name', 'LIKE', '%' . request('search') . '%')
                                  ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                                  ->orWhere('size', 'LIKE', '%' . request('search') . '%')
                                  ->orWhere('pay_rate', 'LIKE', '%' . request('search') . '%')
                                  ->orWhere('product_code', 'LIKE', '%' . request('search') . '%');
                      });
                })->orderBy('id', 'DESC')->where(Parent::branch_array());
         $data= $raw_query->get();
         if(request('page')>0){
             $data=$raw_query->paginate(20);
         }
          return response()->json(['results' => $data]);
    }
  

    function searchItem(){
          $data = PayrollProduct::when(request('search', '') != '', function ($query) {
                 $query->where('name', 'LIKE', '%' . request('search') . '%')
                      ->orWhere('product_code', 'LIKE', '%' . request('search') );
                
          })->where(Parent::branch_array())->skip(0)->take(200)->get();
          return response()->json($data);
      }

    public function destroy(Request $request)
    {
          $id = $request->route('id');
          $id = PayrollProduct::where('id',$id);
          $id->delete();
          return response()->json(['results' => 'deleted']);
    }
    public function fetchById(Request $request)
    {
          $id = $request->id;
          $data =  PayrollProduct::where('id', $id)->first();

          return response()->json(['results' => $data]);
    }
    function getProdutSelect()
    {
          $product_array = array();
          $array_data =    PayrollProduct::where(Parent::branch_array())->orderBy('id', 'DESC')->get();
        foreach ($array_data as  $value) {
             
                $product_array[] = array("label" => $value['name'], 'id' => $value['id']);
          }
          return $product_array;
    }
    function searchProducts(Request $request){
        
          
          $data = PayrollProduct::where(Parent::branch_array())->where('name', 'LIKE','%'.$request->keyword.'%')->orWhere('product_code', 'LIKE','%'.$request->keyword.'%')->get();
          
          return response()->json($data); 
    }

    function productCode()
    {
        $latest = PayrollProduct::latest()->first();
        if ($latest) {
            $productCode = ($latest->id + 1);

            $isUnique = PayrollProduct::where('product_code', $productCode)->first();
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
        $isUnique = PayrollProduct::where('product_code', $productCode)->first();
        if ($isUnique) {
            $productCode = $this->uniqueCode($productCode);
        }
        return $productCode;
    }

}
