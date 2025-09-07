<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use App\Http\Requests\product\AttributeRequest;
use App\product\ProductAttribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function create(AttributeRequest $request){
        ProductAttribute::create($request->validated());
        return true;

    }
    public function fetch(){

      
      $data = ProductAttribute::when(request('search', '') != '', function ($query) {
                  $query->where(function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%')
                              ->orWhere('attribute', 'LIKE', '%' . request('search') . '%');
                             
                  })
                  ->orWhereHas('product', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%');
                       
                });
            })->with(['product'])->orderBy('id', 'DESC')->paginate(30);


       
        return response()->json(['results'=>$data]);
    }
    public function destroy(Request $request ){
        $id=$request->route('id');
        ProductAttribute::find($id)->delete();
        return true;
    }
}
