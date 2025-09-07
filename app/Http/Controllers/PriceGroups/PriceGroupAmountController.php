<?php

namespace App\Http\Controllers\PriceGroups;

use App\Stock\Stock;
use Illuminate\Http\Request;
use App\Stock\PriceGroupAmount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class PriceGroupAmountController extends BaseController
{
    function create(Request $request)
    {
        $this->validate($request, [

            'selling_price' => 'required|gte:0',
        ]);
        PriceGroupAmount::updateOrCreate(
            [
                'stock_id' => $request->stock_id,
                'price_group_id' => $request->price_group_id
            ],
            [

                'selling_price' => $request->selling_price
            ]
        );
        return response()->json($this->queryPriceGroup($request->price_group_id));
    }
    function queryPriceGroup($price_group_id)
    {
        $query = PriceGroupAmount::with(['stock', 'price_group'])->when(request('search') != '', function ($q) {
            $q->whereHas('stock', function ($query) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            })
                ->orWhereHas('price_group', function ($query) {
                    $query->where('price_group', 'LIKE', '%' . request('search') . '%');
                });
        })->where(['price_group_id' => $price_group_id]);

        // $data=$query->get();
        // if(request('page')>0){
        $data = $query->paginate(20);
        //  }
        return $data;
    }
    function fetch()
    {
        return response()->json($this->queryPriceGroup(request('price_group_id')));
    }
    function fetchExcelSample()
    {
        $res = Stock::get();
        return $res;
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        PriceGroupAmount::where(['id' => $id])->delete();
        return true;
    }

    function uploadPriceGroup(Request $request)
    {

        ini_set('max_execution_time', 2400);
        $request->validate([
            'import_file' => 'required|max:50000'
        ]);
        $folderPath = public_path('uploads');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $file = $request->file('import_file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();


        //  return response()->json(['message' => 'uploaded successfully'], 200);

        $valid_extension = array("csv", 'xls');

        // 2MB in Bytes
        $maxFileSize = 209715200;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // File upload location
                $location = 'uploads';

                // Upload file
                $file->move($folderPath, $filename);

                // Import CSV to Database
                $filepath = public_path($location . "/" . $filename);

                // Reading file
                $file = fopen($filepath, "r");

                $importData_arr = array();
                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                    $num = count($filedata);

                    // Skip first row (Remove below comment if you want to skip the first row)
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                // Insert to MySQL database

                foreach ($importData_arr as $importData) {

                    if ($this->checkIfProductExist($importData[0])) {
                        //product found in db
                        $updateData = array(


                            "selling_price" => $importData[1],

                        );

                        PriceGroupAmount::updateOrCreate([
                            "stock_id" => $importData[0],
                            "price_group_id" => $request->price_group_id
                        ], $updateData);
                    }
                }

                return response()->json(["status" => "success", "message" => "successfully uploaded"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }



        // Redirect to index


    }

    function checkIfProductExist($stock_id)
    {
        $res = Stock::where(['id' => $stock_id])->first();
        return $res ? true : false;
    }
}
