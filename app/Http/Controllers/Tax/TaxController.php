<?php

namespace App\Http\Controllers\Tax;

use App\tax\TaxDept;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxController extends Controller
{
    public function create(Request $request){
        $this->validate($request, [
            'tax_rate' => 'numeric|required|between:0,9999999.99',
            'tax_code' => 'required|max:1|unique:tax_depts,tax_code',
            'formulae'=>'numeric|required|between:0,9999999.99',
            'type'=>'required',
        ]);
        TaxDept::create([
            'tax_rate'=>$request->tax_rate,
        'tax_code'=>$request->tax_code,
        'formulae'=>$request->formulae,
        'type'=>$request->type,
        ]);
        $res=TaxDept::orderBy('id','DESC')->get();
        return response()->json($res);
    }

    public function fetch(){
        $res=TaxDept::orderBy('id','DESC')->get();
        return response()->json($res);  
    }
    public function update(Request $request){
        $this->validate($request, [
            'id'=>'required',
            'tax_rate' => 'numeric|required|between:0,9999999.99',
            'tax_code' => "required|max:1|unique:tax_depts,tax_code,{$request->id},id",
            'formulae'=>'numeric|required|between:0,9999999.99',
            'type'=>'required',
        ]);
        TaxDept::where('id',$request->id)->update([
            'tax_rate'=>$request->tax_rate,
        'tax_code'=>$request->tax_code,
        'formulae'=>$request->formulae,
        'type'=>$request->type,
        ]);
        $res=TaxDept::orderBy('id','DESC')->get();
        return response()->json($res);
    }
}
