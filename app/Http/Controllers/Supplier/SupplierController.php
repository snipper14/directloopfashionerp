<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\BaseController;
use App\Suppliers\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\SupplierRequest;

class SupplierController extends BaseController
{
    public function create(SupplierRequest $request)
    {
        Supplier::create($request->all() + ['branch_id' => Parent::branch_id()]);
        return true;
    }

    public function update(SupplierRequest $request)
    {
        Supplier::where('id', $request->id)->update($request->validated());
        return true;
    }
    public function fetch()
    {


        $raw_query = Supplier::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('company_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('address', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('company_phone', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('postal_address', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('city', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('company_email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('bank_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('bank_branch', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('account_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_person', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_phone', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('pin', 'LIKE', '%' . request('search') . '%');
            });
        })->when(request('sort_supplier'), function ($q) {

            $q->orderBy('company_name', request('sort_supplier'));
        })->orderBy('id', 'DESC');
        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(15);
        }
        return response()->json(['results' => $data]);
    }

    public function fetchAll()
    {
        $data = Supplier::orderBy('company_name')->get();
        return response()->json(['results' => $data]);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        Supplier::find($id)->delete();
        return true;
    }

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

        $valid_extension = array("csv");

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // File upload location
                $location = 'uploads';

                // Upload file
                $file->move($location, $filename);

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

                    if (!$this->checkIfSupplierExist($importData[1])) {
                        //product not in db
                        $insertData = array(
                            "company_name" => $importData[1],
                            "company_phone" => $importData[2],
                            "address" => $importData[3],
                            "postal_address" => $importData[4],
                            "city" => $importData[5],
                            "country" => $importData[6],
                            "company_email" => $importData[7],
                            "bank_name" => $importData[8],
                            "bank_branch" => $importData[9],
                            "account_name" => $importData[10],
                            "account_no" => $importData[12],

                            "contact_person" => $importData[13],
                            "contact_email" => $importData[14],
                            "contact_phone" => $importData[15],
                            "pin" => $importData[16],
                            "credit_limit" => $importData[17],
                            "deduct_wht" => $importData[18],
                            'branch_id' => Parent::branch_id()

                        );
                        Supplier::create($insertData);
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

    function checkIfSupplierExist($customer_name)
    {
        $res = Supplier::where(['company_name' => $customer_name, 'branch_id' => Parent::branch_id()])->first();
        return $res ? true : false;
    }
}
