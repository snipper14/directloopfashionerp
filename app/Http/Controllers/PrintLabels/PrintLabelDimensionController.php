<?php

namespace App\Http\Controllers\PrintLabels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PrintLabel\PrintLabelDimension;
use App\Http\Requests\PrintLabels\PrintLabelDimensionRequest;

class PrintLabelDimensionController extends Controller
{
    function create(PrintLabelDimensionRequest $request){
        PrintLabelDimension::create($request->validated());
        return true;
    }
    function update(PrintLabelDimensionRequest $request){
        PrintLabelDimension::where('id',$request->id)->update($request->validated());
        return true;
    }
    function fetch(){
        $res=PrintLabelDimension::get();
        return response()->json($res);
    }
    public function deleteDimension($id)
{
    $dimension = PrintLabelDimension::findOrFail($id);
    $dimension->delete();

    return response()->json(['message' => 'Label Dimension deleted successfully']);
}
}
