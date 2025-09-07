<?php

namespace App\Http\Controllers\Category;

use Illuminate\Http\Request;
use App\Category\ProductCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Category\ProductCategoryRequest;

class ProductCategoryController extends Controller
{
    function create(Request $request)
    {
        $data = json_decode($request['data'], true);

        if ($request->file != "null") {
            $request->validate([
                'file' => 'mimes:jpeg,jpg,png,webp,gif|required|max:10000'
            ]);
        }

        $rules = [
            'name' => "required|unique:product_categories,name,NULL,id,deleted_at,NULL",
            'department_id' => "required",
            'details' => "max:250",
            'show_pos' => 'required',

        ];
        $customMessages = [];
        $validator = Validator::make($data, $rules, $customMessages);
        if ($request->file != "null") {

            $upload_path = public_path('stock_img');

            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }
            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            ProductCategory::create(

                $validator->validated() + ['banner' => $generated_new_name, 'isSync' => 0]

            );
        } else {
            ProductCategory::create(
                $validator->validated() + ['isSync' => 0]
            );
        }


        $res = ProductCategory::with('department')->orderBy('id', 'DESC')->paginate(50);
        return response()->json($res);
    }

    public function update(Request $request)
    {
        $data = json_decode($request['data'], true);

        if ($request->file != "null") {
            $request->validate([
                'file' => 'mimes:jpeg,jpg,png,webp,gif|required|max:10000'
            ]);
        }

        $rules = [
            'id' => 'required',
            'name' => 'required|unique:product_categories,name,' . $data['id'] . ',id,deleted_at,NULL',
            'department_id' => "required",
            'details' => "max:250",
            'show_pos' => 'required'
        ];
        $customMessages = [];
        $validator = Validator::make($data, $rules, $customMessages);
        if ($request->file != "null") {

            $upload_path = public_path('stock_img');

            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }
            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            $this->deleteLogoImageFiles($data['id']);

            ProductCategory::where('id',  $data['id'])->update(

                $validator->validated() + ['banner' => $generated_new_name]

            );
        } else {
            ProductCategory::where('id',  $data['id'])->update(
                $validator->validated()
            );
        }


        $res = ProductCategory::with('department')->orderBy('id', 'DESC')->paginate(50);
        return response()->json($res);
    }
    public function deleteLogoImageFiles($id)
    {

        $item = ProductCategory::where('id', $id)->first();

        $file = $item->banner;
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
        $this->deleteLogoImageFiles($id);
        ProductCategory::find($id)->delete();

        return response()->json(['message' => "record deleted"]);
    }

    public function fetch()
    {
        $res = ProductCategory::with('department')
            ->when(request('department_id', '') != '', function ($query) {

                $query->where('department_id',  request('department_id'));
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('details', 'LIKE', '%' . request('search') . '%');
                });
            })->orderBy('id', 'DESC');
        $data = request('page') > 0 ? $res->paginate(50) : $res->get();
        return response()->json($data);
    }
    public function fetchAll()
    {
        $main_query = ProductCategory::with('department')
            ->when(request('department_id', '') != '', function ($query) {

                $query->where('department_id',  request('department_id'));
            });

        if (request('show_pos') == "show_pos") {
            $main_query->where('show_pos', 1);
        }
        $res = $main_query->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }
}
