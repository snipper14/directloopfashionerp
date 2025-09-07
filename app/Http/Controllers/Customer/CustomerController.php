<?php

namespace App\Http\Controllers\Customer;

use App\Promo\Promo;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Customer\CustomerRequest;

class CustomerController extends BaseController
{
    function getCustomerBalance(){
         $customer_id = request('customer_id');
        $res = CustomerLedger::with(['customer'])->where('customer_id', $customer_id)->selectRaw('*,SUM(debit-credit) AS total_balance')->first();

        $pending_amount = 0;
        if ($res) {
            $pending_amount = $res->total_balance;
        }
        return response()->json(["pending_amount"=>$pending_amount,'credit_limit'=>$res->customer->credit_limit,"company_name"=>$res->customer->company_name]);
    }
    public function create(CustomerRequest $request)
    {
        $account_code = "";
        if ($request->acc_code) {
            $account_code = $request->acc_code;
        } else {
            $account_code = Parent::customerCode();
        }
        $res =  Customer::create(['acc_code' => $account_code] + $request->validated() + ["branch_id" => Parent::branch_id()]);
        return response()->json($res);
    }

    public function update(CustomerRequest $request)
    {
        Customer::where('id', $request->id)->update($request->validated());
        return true;
    }
    public function fetch()
    {


        $main_query = Customer::with(['price_group'])->when(request('search', '') != '', function ($query) {
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
                    ->orWhere('pin', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('price_group', function ($q) {
                        $q->where('price_group', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->when(request('sort_customer'), function ($q) {

            $q->orderBy('company_name', request('sort_customer'));
        })->orderBy('id', 'DESC');
        $data =  $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(30);
        }
        return response()->json(['results' => $data]);
    }
    public function searchCustomer()
    {


        $main_query = Customer::when(request('search', '') != '', function ($query) {
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
                    ->orWhere('contact_phone', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC');
        $data =  $main_query->orderBy('id', 'DESC')->skip(0)->take(10)->get();

        return response()->json($data);
    }

    public function fetchAll()
    {
        $data = Customer::orderBy('company_name')->get();
        return response()->json(['results' => $data]);
    }

    function getCustomerSelect()
    {
        $product_array = array();
        $array_data =      Customer::orderBy('company_name')->get();
        foreach ($array_data as  $value) {

            $product_array[] = array("label" => $value['company_name'], 'id' => $value['id']);
        }
        return $product_array;
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

                    if (!$this->checkIfCustomerExist($importData[1])) {
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
                            "account_no" => $importData[11],
                            "acc_code" => $importData[12],
                            "contact_person" => $importData[13],
                            "contact_email" => $importData[14],
                            "contact_phone" => $importData[15],
                            "pin" => $importData[16],
                            "credit_limit" => $importData[17],
                            "vat_deduction" => $importData[18],
                            'branch_id' => Parent::branch_id()

                        );
                        Customer::create($insertData);
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

    function checkIfCustomerExist($customer_name)
    {
        $res = Customer::where(['company_name' => $customer_name])->first();
        return $res ? true : false;
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        Customer::find($id)->delete();
        return true;
    }

    function fetchPromos(Request $request){
      $res=  Promo::get();
      return $res;
    }
    function getRedeemablePoints(Request $request)
    {
        $customer_id = $request->customer_id;
        $res = Customer::where('id', $customer_id)->selectRaw('(loyalty_points-redeemed_loyalty_points)  As redeemable_points')->first();
        return $res->redeemable_points;
    }
}
