<?php

namespace App\Http\Controllers\Etims;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\EtimStock\ItemClassification;
use App\Http\Controllers\BaseController;

class EtimsItemClassificationCodeController extends BaseController
{


    function create(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                Rule::unique('item_classifications')->whereNull('deleted_at'),
            ],
            'code' => 'required',
            'description' => [
                'required',
                Rule::unique('item_classifications')->whereNull('deleted_at'),
            ],
        ];
        $customMessages = [];
        $this->validate($request, $rules, $customMessages);
        ItemClassification::create([
            'description' => $request->description,
            'code' => $request->code,
            'name' => $request->name
        ]);
        return true;
    }

    function fetch()
    {

        $main_query = ItemClassification::when(request('search', '') != '', function ($q) {

            $q->where('description', 'LIKE', '%' . request('search') . '%')
                ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                ->orWhere('name', 'LIKE', '%' . request('search') . '%');
        });
        $res = $main_query->orderBy('id', 'DESC')->get();
        if (request('page') > 0) {
            $res = $main_query->orderBy('id', 'DESC')->paginate(20);
        }
        //  print_r(DB::getQueryLog());
        return response()->json($res);
    }

    function destroy(Request $request)
    {
        $id = $request->route('id');
        ItemClassification::where('id', $id)->delete();
        return true;
    }
    function update(Request $request){
        $rules = [
            'name' => [
                'required',
                Rule::unique('item_classifications')->where(function ($query) {
                   $query->whereNull('deleted_at');
                })->ignore($request->id),
            ],
            'id'=>'required',
            'code' => 'required',
            'description' => [
                'required',
                Rule::unique('item_classifications')->where(function ($query) {
                    $query->whereNull('deleted_at');
                 })->ignore($request->id),
            ],
        ];
        $customMessages = [];
        $this->validate($request, $rules, $customMessages);
        ItemClassification::where('id',$request->id)->update([
            'description' => $request->description,
            'code' => $request->code,
            'name' => $request->name
        ]);
        return true;
    }
}
