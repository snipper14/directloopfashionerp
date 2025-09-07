<?php

namespace App\Http\Controllers\Lab;

use Carbon\Carbon;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\Customer\Customer;
use App\Medical\LabResult;
use Illuminate\Http\Request;
use App\Medical\LabProductSale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Lab\LabRequest;
use Illuminate\Support\Facades\Validator;

class LabController extends BaseController
{
    function create(Request $request)
    {
        $res = null;
        DB::transaction(function () use ($request, &$res) {
            $data = json_decode($request['data'], true);
            if ($request->imaging_results != "null") {
                $request->validate([
                    'imaging_results' => 'mimes:jpeg,jpg,png,gif,docx,pdf|required|max:10000'
                ]);
            }
            $rules = [
                'customer_id' => "required",
                'patient_name' => 'required',
                'blood_test' => 'nullable',
                'other_test_results' => "nullable",
                'lab_tech' => "nullable",
                'blood_pressure' => "nullable",
                'heart_rate' => 'nullable',
                'respiratory_rate' => 'nullable',
                'temperature' => 'nullable',
                'height_and_weight' => "nullable",



            ];
            $validator = Validator::make($data, $rules);
            $uniqueID = $this->entryCode(); //Parent::uniqueID();
            $res = null;
            if ($request->imaging_results != "null") {
                $upload_path = public_path('upload');
                $generated_new_name = time() . '.' . $request->imaging_results->getClientOriginalExtension();
                $request->imaging_results->move($upload_path, $generated_new_name);
                $res =  LabResult::create($validator->validated() + Parent::user_branch_id() +
                    ['imaging_results' => $generated_new_name, 'entry_code' => $uniqueID]);
            } else {

                $res =  LabResult::create($validator->validated() + ['entry_code' => $uniqueID] + Parent::user_branch_id());
            }

            $pres_res = LabProductSale::where(['customer_id' => $data['customer_id'], 'status' => 'pending'])->get();
            foreach ($pres_res as $value) {
                $stock = Stock::with(['tax_dept'])->find($value['stock_id']);
                $customer = Customer::find($data['customer_id']);

                $tax_rate = $stock->tax_dept->tax_rate;
                $row_amount = ($stock->selling_price * $value['qty']);
                $row_tax = Parent::calculateTax($tax_rate, $row_amount);
                POSOrder::create(
                    [
                        'stock_id' => $value['stock_id'],

                        'order_date' => Parent::currentDate(),
                        'qty' => $value['qty'],
                        'selling_price' => $stock->selling_price,
                        'row_amount' => ($stock->selling_price * $value['qty']),
                        'row_vat' => $row_tax,
                        'order_no' => $uniqueID,
                        'tax_rate' => $tax_rate,
                        'product_name' => $stock->product_name,
                        'code' => $stock->code,
                        'batch_no' => '',
                        'customer_id' => $data['customer_id'],
                        'customer_name' => $customer->company_name,


                    ]
                        + Parent::user_branch_id()
                );
            }
            LabProductSale::where(['customer_id' => $data['customer_id'], 'status' => 'pending'])->update([
                'entry_code' => $uniqueID, 'lab_result_id' => $res->id, 'status' => 'completed'
            ]);
        }, 5);
        return response()->json($res);
    }

    public function update(Request $request)
    {
        $res = null;
        DB::transaction(function () use ($request, &$res) {
            $data = json_decode($request['data'], true);
            if ($request->imaging_results != "null") {
                $request->validate([
                    'imaging_results' => 'mimes:jpeg,jpg,png,gif,docx,pdf|required|max:10000'
                ]);
            }
            $rules = [
                'customer_id' => "required",
                'patient_name' => 'required',
                'blood_test' => 'nullable',
                'other_test_results' => "nullable",
                'lab_tech' => "nullable",
                'blood_pressure' => "nullable",
                'heart_rate' => 'nullable',
                'respiratory_rate' => 'nullable',
                'temperature' => 'nullable',
                'height_and_weight' => "nullable",
            ];
            $validator = Validator::make($data, $rules);

            $res = null;
            if ($request->imaging_results != "null") {
                $upload_path = public_path('upload');
                $generated_new_name = time() . '.' . $request->imaging_results->getClientOriginalExtension();
                $request->imaging_results->move($upload_path, $generated_new_name);
                $this->deleteImageFiles($data['entry_code']);
                $res =  LabResult::where('entry_code', $data['entry_code'])->update($validator->validated() + Parent::user_branch_id() +
                    ['imaging_results' => $generated_new_name]);
            } else {

                $res =  LabResult::where('entry_code', $data['entry_code'])->update($validator->validated()  + Parent::user_branch_id());
            }

         

            $pres_res = LabProductSale::where(['customer_id' => $data['customer_id'], 'entry_code' =>  $data['entry_code']])->get();
            POSOrder::where(['order_no' => $data['entry_code']])->delete();
            foreach ($pres_res as $value) {
                echo ">>>>>" . $value['stock_id'];
                $stock = Stock::with(['tax_dept'])->find($value['stock_id']);
                $customer = Customer::find($data['customer_id']);

                $tax_rate = $stock->tax_dept->tax_rate;
                $row_amount = ($stock->selling_price * $value['qty']);
                $row_tax = Parent::calculateTax($tax_rate, $row_amount);
                POSOrder::create(
                    [
                        'stock_id' => $value['stock_id'],

                        'order_date' => Parent::currentDate(),
                        'qty' => $value['qty'],
                        'selling_price' => $stock->selling_price,
                        'row_amount' => ($stock->selling_price * $value['qty']),
                        'row_vat' => $row_tax,
                        'order_no' => $data['entry_code'],
                        'tax_rate' => $tax_rate,
                        'product_name' => $stock->product_name,
                        'code' => $stock->code,
                        'batch_no' => '',
                        'customer_id' => $data['customer_id'],
                        'customer_name' => $customer->company_name,


                    ]
                        + Parent::user_branch_id()
                );
            }
        }, 5);
        return response()->json($res);
    }
    function addLabProducts(LabRequest $request)
    {
        LabProductSale::updateOrCreate([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,
            'status' => 'pending'
        ], $request->validated() + Parent::user_branch_id());
        $res = LabProductSale::where(['customer_id' => $request->customer_id, 'status' => 'pending'])->get();
        return response()->json($res);
    }
    function updateLabProducts(LabRequest $request)
    {
        LabProductSale::updateOrCreate([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,
            'entry_code' => $request->entry_code
        ], ['status' => 'completed'] + $request->validated() + Parent::user_branch_id());
        $res = LabProductSale::where(['customer_id' => $request->customer_id,  'entry_code' => $request->entry_code])->get();
        return response()->json($res);
    }
    function entryCode()
    {


        $latest = LabProductSale::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'LAB' . sprintf('%04d', $string);
        $isUnique = LabProductSale::where('entry_code', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'LAB' . ($latest->id + 1);
        }
        return  $orderNo;
    }
    function fetchPendingProducts()
    {
        $res = LabProductSale::where(['customer_id' => request('customer_id'), 'status' => 'pending'])->get();
        return $res;
    }

    function destroy(Request $request)
    {
        $id = $request->route('id');
        LabProductSale::where(['id' => $id])->delete();
        return true;
    }

    function fetchLabHistory()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = LabResult::where(['customer_id' => request('customer_id')])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('entry_code', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('patient_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('blood_test', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('other_test_results', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('lab_tech', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('blood_test', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('other_test_results', 'LIKE', '%' . request('search') . '%')

                        ->orWhere('blood_pressure', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('heart_rate', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('respiratory_rate', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('temperature', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('height_and_weight', 'LIKE', '%' . request('search') . '%')

                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            });
        $main_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('created_at', [$from, $to]);
            }
        );
        $data =  $main_query->latest()->get();
        if (request('page') > 0) {
            $data =  $main_query->paginate(30);
        }
        return response()->json($data);
    }

    function deleteLabRecord(Request $request)
    {
        $entry_code = $request->entry_code;
        $this->deleteImageFiles($entry_code);
        LabResult::where(['entry_code' => $entry_code])->delete();
        LabProductSale::where(['entry_code' => $entry_code])->delete();

        return true;
    }

    public function deleteImageFiles($entry_code)
    {
        $item = LabResult::where(['entry_code' => $entry_code])->first();

        $file = $item->imaging_results;
        if ($file) {
            $file_path = public_path() . '/upload/' . $file;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    public function fetchLabResultByEntryCode()
    {
        $lab_result = LabResult::where(['entry_code' => request('entry_code'), 'customer_id' => request('customer_id')])->first();

        return response()->json(['lab_result' => $lab_result]);
    }

    public function fetchCompletedLabresult()
    {
        $lab_product_result = LabProductSale::with(['stock'])->where(['entry_code' => request('entry_code'), 'customer_id' => request('customer_id')])->get();
        return response()->json($lab_product_result);
    }
}
