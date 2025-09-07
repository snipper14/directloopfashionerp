<?php

namespace App\Http\Controllers\Stock;

use App\Unit\Unit;
use App\Stock\Stock;
use App\tax\TaxDept;
use App\dept\Department;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Category\ProductCategory;
use App\EtimStock\EtimsItemPackagingCode;
use App\EtimStock\EtimsItemType;
use App\EtimStock\EtimsOriginCountry;
use App\EtimStock\ItemClassification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Location\Shelf;
use App\Stock\Branchshelf;
use App\Suppliers\Supplier;

class ImportStockExcelController extends BaseController
{
    public function import(Request $request)
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

                    if ($this->checkIfProductExist($importData[2])) {
                        //product found in db

                        $unit = Unit::updateOrCreate(['name' => $importData[4]], [
                            'name' => $importData[4],
                            'code' => $importData[3],
                            'user_id' => Parent::user_id()
                        ]);
                        $category = ProductCategory::updateOrCreate(['name' => $importData[5]], [
                            'name' => $importData[5]

                        ]);
                        $tax_dept = TaxDept::updateOrCreate(['tax_rate' => $importData[10]], [
                            'tax_code' => $importData[11]

                        ]);
                        $department = Department::updateOrCreate(['department' => $importData[12]], [
                            'department' => $importData[12],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);

                        $shelf = Shelf::updateOrCreate(['name' => $importData[16]], [
                            'name' => $importData[16],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);


                        $classcode = ItemClassification::updateOrCreate(['name' => $importData[18]], [
                            'code' => $importData[17],
                            'description' => $importData[18]

                        ]);
                        $itemType = EtimsItemType::updateOrCreate(['name' => $importData[20]], [
                            'code' => $importData[19],
                            'description' => $importData[20]

                        ]);
                        $itemCountryOrigin = EtimsOriginCountry::updateOrCreate(['name' => $importData[22]], [
                            'code' => $importData[21],
                            'description' => $importData[22]

                        ]);
                        $etimsItemPackagingCode = EtimsItemPackagingCode::updateOrCreate(['name' => $importData[24]], [
                            'code' => $importData[23],
                            'description' => $importData[24]

                        ]);


                        $updateData = array(

                            "code" => $importData[1],
                            "product_name" => $importData[2],
                            "name" => $importData[2],
                            'unit_id' => $unit->id,
                            'product_category_id' => $category->id,
                            'shelf_id' => $shelf->id,
                            'qty' => 0,
                            'reorder_qty' => $importData[6],
                            'purchase_price' => $importData[7],
                            'selling_price' => $importData[8],
                            'wholesale_price' => $importData[9],
                            'tax_dept_id' => $tax_dept->id,
                            'inventory_type' => $importData[14],
                            'description' => $importData[15],
                            'item_classification_id' => $classcode->id,
                            'etims_item_type_id' => $itemType->id,
                            'etims_origin_country_id' => $itemCountryOrigin->id,
                            'etims_item_packaging_code_id' => $etimsItemPackagingCode->id
                        );

                        $stock = Stock::create($updateData + ['user_id' => Parent::user_id()]);
                        if ($stock) {
                            Inventory::updateOrCreate(
                                ['stock_id' => $stock->id, 'department_id' => $department->id, 'branch_id' => Parent::branch_id()],
                                ['qty' =>  $importData[13]]
                            );
                        }
                    } else {
                        //echo "product  found";
                        //update inventory only 
                        $stock = Stock::where(["name" => $importData[2]])->first();
                        $department = Department::updateOrCreate(['department' => $importData[12]], [
                            'department' => $importData[12],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);
                        if ($stock) {
                            Inventory::updateOrCreate(
                                ['stock_id' => $stock->id, 'department_id' => $department->id, 'branch_id' => Parent::branch_id()],
                                ['qty' => $importData[13]]
                            );
                        }
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


// Put these helpers anywhere in the same class (or a trait)

protected function normalizeCell($v): string
{
    // Trim & ensure scalar
    $s = is_scalar($v) ? (string) $v : '';
    $s = trim($s);

    // Detect common encodings and convert to UTF-8 (ignore bad bytes)
    $enc = mb_detect_encoding($s, ['UTF-8','Windows-1252','ISO-8859-1','ASCII'], true) ?: 'Windows-1252';
    $s = iconv($enc, 'UTF-8//IGNORE', $s);

    // As an extra guard, drop remaining invalid bytes
    if (!mb_check_encoding($s, 'UTF-8')) {
        $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
    }

    // Optional: normalize whitespace
    $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

    return $s;
}

protected function normalizeRow(array $row): array
{
    // Call normalize on each column value
    return array_map(fn($v) => $this->normalizeCell($v), $row);
}

// Keep this name consistent everywhere (pick one column; here I use product_name)
function checkIfProductExist($product_name)
{
    return ! Stock::where('product_name', $product_name)->exists();
}

function importNormal(Request $request)
{
    ini_set('max_execution_time', 2400);

    $request->validate([
        'import_file' => 'required|max:50000'
    ]);

    $folderPath = public_path('uploads');
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0775, true);
    }

    $file = $request->file('import_file');
    $filename   = $file->getClientOriginalName();
    $extension  = strtolower($file->getClientOriginalExtension());
    $fileSize   = $file->getSize();

    $valid_extension = ['csv', 'xls'];
    $maxFileSize     = 209715200; // 200MB

    if (!in_array($extension, $valid_extension)) {
        return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
    }
    if ($fileSize > $maxFileSize) {
        return response()->json(["status" => "error", "message" => "File too large. File must be less than 200MB."], 200);
    }

    // Save file
    $file->move($folderPath, $filename);
    $filepath = public_path('uploads/'.$filename);

    // Read CSV
    $fh = fopen($filepath, 'r');
    if (!$fh) {
        return response()->json(["status" => "error", "message" => "Unable to open uploaded file."], 200);
    }

    $importDataArr = [];
    $i = 0;
    while (($filedata = fgetcsv($fh, 0, ",")) !== false) {
        if ($i === 0) { // skip header
            $i++;
            continue;
        }
        // Normalize the whole row to UTF-8 now
        $importDataArr[] = $this->normalizeRow($filedata);
        $i++;
    }
    fclose($fh);

    foreach ($importDataArr as $importData) {
        // Defensive: ensure indexes exist even if some cells are missing
        $get = fn($idx, $default = null) => $importData[$idx] ?? $default;

        $code           = $get(1);
        $productName    = $get(2);
        $unitName       = $get(3);
        $categoryName   = $get(4);
        $reorderQty     = $get(5) ?: 0;
        $purchasePrice  = $get(6) ?: 0;
        $sellingPrice   = $get(7) ?: 0;
        $wholesalePrice = $get(8) ?: 0;
        $taxRate        = $get(9) ?: 0;
        $taxCode        = $get(10);
        $departmentName = $get(11);
        $qtyToAdd       = $get(12) ?: 0;
        $inventoryType  = $get(13);
        $description    = $get(14);
        $shelfName      = $get(15);
        $supplierName   = $get(16);

        // Skip empty product rows safely
        if (!$productName) {
            continue;
        }

        // Create/lookup refs (all normalized to UTF-8)
        $unit = Unit::updateOrCreate(['name' => $unitName], [
            'name' => $unitName,
            'user_id' => Parent::user_id(),
        ]);

        $category = ProductCategory::updateOrCreate(['name' => $categoryName], [
            'name' => $categoryName,
        ]);

        $taxDept = TaxDept::updateOrCreate(['tax_rate' => $taxRate], [
            'tax_code' => $taxCode,
        ]);

        $department = Department::updateOrCreate(['department' => $departmentName], [
            'department' => $departmentName,
            'user_id' => Parent::user_id(),
            'branch_id' => Parent::branch_id(),
        ]);

        $shelf = Shelf::updateOrCreate(['name' => $shelfName], [
            'name' => $shelfName,
            'user_id' => Parent::user_id(),
            'branch_id' => Parent::branch_id(),
        ]);

        $supplier = Supplier::updateOrCreate(['company_name' => $supplierName], [
            'company_name' => $supplierName,
            'branch_id' => Parent::branch_id(),
        ]);

        if ($this->checkIfProductExist($productName)) {
            // Create new Stock
            $payload = [
                "code"                => $code,
                "product_name"        => $productName,
                "name"                => $productName, // keep in sync or drop one column long-term
                'unit_id'             => $unit->id,
                'product_category_id' => $category->id,
                'shelf_id'            => $shelf->id,
                'qty'                 => 0,
                'reorder_qty'         => $reorderQty,
                'purchase_price'      => $purchasePrice,
                'selling_price'       => $sellingPrice,
                'wholesale_price'     => $wholesalePrice,
                'tax_dept_id'         => $taxDept->id,
                'inventory_type'      => $inventoryType,
                'description'         => $description,
                'supplier_id'         => $supplier->id,
            ];

            // Normalize *again* just in case something slipped through
            foreach ($payload as $k => $v) {
                if (is_string($v)) {
                    $payload[$k] = $this->normalizeCell($v);
                }
            }

            $stock = Stock::create($payload + ['user_id' => Parent::user_id()]);
            if ($stock) {
                Inventory::updateOrCreate(
                    ['stock_id' => $stock->id, 'department_id' => $department->id, 'branch_id' => Parent::branch_id()],
                    ['qty' => $qtyToAdd]
                );
            }
        } else {
            // Update inventory for existing product
            $stock = Stock::where('product_name', $productName)->first();
            if ($stock) {
                Inventory::updateOrCreate(
                    ['stock_id' => $stock->id, 'department_id' => $department->id, 'branch_id' => Parent::branch_id()],
                    ['qty' => $qtyToAdd]
                );
            }
        }
    }

    return response()->json(["status" => "success", "message" => "successfully uploaded"], 200);
}





    function importNormalUpdateStockCode(Request $request)
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

                    if ($this->checkIfProductExist($importData[2])) {
                        //product found in db


                        $updateData = array(

                            "code" => $importData[1],

                        );

                        $stock = Stock::create($updateData + ['user_id' => Parent::user_id()]);
                    } else {

                        $updateData = array(

                            "code" => $importData[1],

                        );

                        $stock = Stock::where(["product_name" => $importData[2]])->update($updateData);

                        //echo "product  found";
                        //update inventory only 

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


    //edit import etims
    public function isProduAvailable($id)
    {

        $res = Stock::where(['id' => $id])->first();
        return $res ? true : false;
    }
    public function isProductNameAvailable($name)
    {
        $res = Stock::where(['name' => $name])->first();
        return $res ? true : false;
    }
    public function importEditEtims(Request $request)
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

                    if ($this->isProduAvailable($importData[0])) {
                        //product found in db

                        $unit = Unit::updateOrCreate(['name' => $importData[4]], [
                            'name' => $importData[4],
                            'code' => $importData[3],
                            'user_id' => Parent::user_id()
                        ]);
                        $category = ProductCategory::updateOrCreate(['name' => $importData[5]], [
                            'name' => $importData[5]

                        ]);
                        $tax_dept = TaxDept::updateOrCreate(['tax_rate' => $importData[10]], [
                            'tax_code' => $importData[11]

                        ]);
                        $department = Department::updateOrCreate(['department' => $importData[12]], [
                            'department' => $importData[12],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);

                        $shelf = Shelf::updateOrCreate(['name' => $importData[16]], [
                            'name' => $importData[16],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);


                        $classcode = ItemClassification::updateOrCreate(['name' => $importData[18]], [
                            'code' => $importData[17],
                            'description' => $importData[18]

                        ]);
                        $itemType = EtimsItemType::updateOrCreate(['name' => $importData[20]], [
                            'code' => $importData[19],
                            'description' => $importData[20]

                        ]);
                        $itemCountryOrigin = EtimsOriginCountry::updateOrCreate(['name' => $importData[22]], [
                            'code' => $importData[21],
                            'description' => $importData[22]

                        ]);
                        $etimsItemPackagingCode = EtimsItemPackagingCode::updateOrCreate(['name' => $importData[24]], [
                            'code' => $importData[23],
                            'description' => $importData[24]

                        ]);


                        $updateData = array(

                            "code" => $importData[1],
                            "product_name" => $importData[2],
                            "name" => $importData[2],
                            'unit_id' => $unit->id,
                            'product_category_id' => $category->id,
                            'shelf_id' => $shelf->id,
                            'qty' => 0,
                            'reorder_qty' => $importData[6],
                            'purchase_price' => $importData[7],
                            'selling_price' => $importData[8],
                            'wholesale_price' => $importData[9],
                            'tax_dept_id' => $tax_dept->id,
                            'inventory_type' => $importData[14],
                            'description' => $importData[15],
                            'item_classification_id' => $classcode->id,
                            'etims_item_type_id' => $itemType->id,
                            'etims_origin_country_id' => $itemCountryOrigin->id,
                            'etims_item_packaging_code_id' => $etimsItemPackagingCode->id
                        );

                        Stock::updateOrCreate(
                            ["id" => $importData[0]],
                            $updateData + ['user_id' => Parent::user_id()]
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



        // Redirect to index

    }

    public function importInventory(Request $request)
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
                    } else {

                        $stock = Stock::where(["name" => $importData[0]])->first();
                        $department = Department::updateOrCreate(['department' => $importData[2]], [
                            'department' => $importData[2],
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);
                        if ($stock) {
                            Inventory::updateOrCreate(
                                ['stock_id' => $stock->id, 'department_id' => $department->id, 'branch_id' => Parent::branch_id()],
                                ['qty' => $importData[1]]
                            );
                        }
                    }
                }

                return response()->json(["status" => "success", "message" => "Inventory successfully uploaded"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }



        // Redirect to index

    }

    public function importStockCode(Request $request)
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
                    } else {

                        $stock = Stock::where(["name" => $importData[0]])->first();

                        if ($stock) {
                            Stock::where('name', $importData[0])->update(
                                ['code' => $importData[1]]
                            );
                        }
                    }
                }

                return response()->json(["status" => "success", "message" => "Inventory successfully uploaded"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }



        // Redirect to index

    }
}
