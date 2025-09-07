<?php

namespace App\Http\Controllers\Etims;

use App\EtimStock\EtimsOriginCountry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class EtimsCountryOriginController extends Controller
{
    function create(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                Rule::unique('etims_origin_countries')->whereNull('deleted_at'),
            ],
            'code' => 'required',
            'description' => [
                'required',
                Rule::unique('etims_origin_countries')->whereNull('deleted_at'),
            ],
        ];
        $customMessages = [];
        $this->validate($request, $rules, $customMessages);
        EtimsOriginCountry::create([
            'description' => $request->description,
            'code' => $request->code,
            'name' => $request->name
        ]);
        return true;
    }

    function fetch()
    {

        $main_query = EtimsOriginCountry::when(request('search', '') != '', function ($q) {

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
        EtimsOriginCountry::where('id', $id)->delete();
        return true;
    }
    function update(Request $request){
        $rules = [
            'name' => [
                'required',
                Rule::unique('etims_origin_countries')->where(function ($query) {
                   $query->whereNull('deleted_at');
                })->ignore($request->id),
            ],
            'id'=>'required',
            'code' => 'required',
            'description' => [
                'required',
                Rule::unique('etims_origin_countries')->where(function ($query) {
                    $query->whereNull('deleted_at');
                 })->ignore($request->id),
            ],
        ];
        $customMessages = [];
        $this->validate($request, $rules, $customMessages);
        EtimsOriginCountry::where('id',$request->id)->update([
            'description' => $request->description,
            'code' => $request->code,
            'name' => $request->name
        ]);
        return true;
    }
}
