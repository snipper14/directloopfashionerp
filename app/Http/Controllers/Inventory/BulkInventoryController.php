<?php

namespace App\Http\Controllers\Inventory;

use App\Stock\Stock;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Inventory\BulkStockTake;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

class BulkInventoryController extends BaseController
{
    public function create(Request $request)
    {
        ini_set('max_execution_time', 2400);

        $validator = Validator::validate($request->all(), [
            'department_id' => 'required',
            'original_array_before_search' => 'required|array',
            'original_array_before_search.*.counted_qty' => 'required|numeric',
        ], [
            'original_array_before_search.*.counted_qty.gte' => 'The counted quantity for product :product_name must be greater than or equal to 0.',
        ]);

        $stocktake_code = md5(uniqid());
        for ($i = 0; $i < count($request->original_array_before_search); $i++) {
            $value = $request->original_array_before_search[$i];

            Inventory::updateOrCreate(
                ['stock_id' => $value['stock_id'], 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                ['qty' =>  $value['counted_qty']]
            );
            $insert_array = [
                'stock_id' => $value['stock_id'],
                'department_id' => $request->department_id,
                'stocktake_date' => Parent::currentDateTime(),
                'current_qty' => $value['current_qty'],
                'counted_qty' => $value['counted_qty'],
                'selling_price' => $value['selling_price'],
                'purchase_price' => $value['purchase_price'],
                'stocktake_code' => $stocktake_code
            ] + Parent::user_branch_id();
            BulkStockTake::create($insert_array);
        }
        return true;
    }

    function fetchGrouped()
    {
        $startDate = request('from');
        $endDate = request('to');
        $perPage = request('page');
        $resultsWithDateFilter = BulkStockTake::filterAndGroupByDate($startDate, $endDate, $perPage);
        return response()->json($resultsWithDateFilter);
    }

    function fetchDetails()
    {

        $perPage = request('page');
        $data = BulkStockTake::fetchDetails($perPage);
        return response()->json($data);
    }




    function importCSV(Request $request)
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
        $maxFileSize = 2097152;

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
                $stocktake_code = md5(uniqid());
                foreach ($importData_arr as $importData) {




                    $stock_id = $importData[0];
                    $department_id = $importData[1];
                    $counted_qty = $importData[6];
                    $current_qty = 0;
                    $inventory = Inventory::where(['department_id' => $department_id, 'stock_id' => $stock_id])->first();
                    if ($inventory) {
                        $current_qty = $inventory->qty;
                    }
                    $stock = Stock::where(['id' => $importData[0]])->first();

                    if ($stock) {
                        $insert_array = [
                            'stock_id' => $stock_id,
                            'department_id' =>  $department_id,
                            'stocktake_date' => Parent::currentDateTime(),
                            'current_qty' => $current_qty,
                            'counted_qty' => $counted_qty,
                            'selling_price' =>  $stock->selling_price,
                            'purchase_price' => $stock->purchase_price,
                            'stocktake_code' => $stocktake_code
                        ] + Parent::user_branch_id();
                        BulkStockTake::create($insert_array);
                        Inventory::updateOrCreate(
                            ['stock_id' => $stock_id, 'department_id' => $department_id, 'branch_id' => Parent::branch_id()],
                            ['qty' => $counted_qty]
                        );
                    }
                }

                return response()->json(["status" => "success", "message" => "successfully uploaded"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }
    }
}
