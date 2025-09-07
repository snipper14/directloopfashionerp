<?php

namespace App\Http\Controllers\Medical;

use Carbon\Carbon;
use App\Stock\Stock;
use App\tax\TaxDept;
use App\POS\POSOrder;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\Medical\Prescription;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Medical\PatientMedicalRecord;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Medical\PrescriptionRequest;
use App\Medical\LabResult;

class MedicalRecordController extends BaseController
{
    //
    public function create(Request $request)
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
                'healthcare_provider_report' => 'nullable',
                'symptoms_complaints' => "nullable",
                'treatment_plans' => "nullable",
                'purpose_of_visit' => "nullable",
                'visiting_health_provider' => 'nullable',
                'date_time_appointments' => 'nullable',
                'current_medication' => "nullable",
                'family_medical_history' => "nullable",
                'past_surgeries_medical_procedures' => 'nullable',
                'allergies' => 'nullable',
                'pre_existing_medical_conditions' => 'nullable',




            ];
            $validator = Validator::make($data, $rules);
            $uniqueID = $this->entryCode(); //Parent::uniqueID();
            $res = null;
            if ($request->imaging_results != "null") {
                $upload_path = public_path('upload');
                $generated_new_name = time() . '.' . $request->imaging_results->getClientOriginalExtension();
                $request->imaging_results->move($upload_path, $generated_new_name);
                $res =  PatientMedicalRecord::create($validator->validated() + Parent::user_branch_id() +
                    ['imaging_results' => $generated_new_name, 'entry_code' => $uniqueID]);
            } else {
                $res =  PatientMedicalRecord::create($validator->validated() + ['entry_code' => $uniqueID] + Parent::user_branch_id());
            }
            DB::enableQueryLog();
            $pres_res = Prescription::where(['customer_id' => $data['customer_id'], 'status' => 'pending'])->get();
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
            Prescription::where(['customer_id' => $data['customer_id'], 'status' => 'pending'])->update([
                'entry_code' => $uniqueID, 'patient_medical_record_id' => $res->id, 'status' => 'completed'
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
                'healthcare_provider_report' => 'nullable',
                'symptoms_complaints' => "nullable",
                'treatment_plans' => "nullable",
                'purpose_of_visit' => "nullable",
                'visiting_health_provider' => 'nullable',
                'date_time_appointments' => 'nullable',
                'current_medication' => "nullable",
                'family_medical_history' => "nullable",
                'past_surgeries_medical_procedures' => 'nullable',
                'allergies' => 'nullable',
                'pre_existing_medical_conditions' => 'nullable',
                'entry_code' => "required"
            ];
            $validator = Validator::make($data, $rules);

            $res = null;
            if ($request->imaging_results != "null") {
                $upload_path = public_path('upload');
                $generated_new_name = time() . '.' . $request->imaging_results->getClientOriginalExtension();
                $request->imaging_results->move($upload_path, $generated_new_name);
                $this->deleteImageFiles($data['entry_code']);
                $res =  PatientMedicalRecord::where('entry_code', $data['entry_code'])->update($validator->validated() + Parent::user_branch_id() +
                    ['imaging_results' => $generated_new_name]);
            } else {

                $res =  PatientMedicalRecord::where('entry_code', $data['entry_code'])->update($validator->validated()  + Parent::user_branch_id());
            }



            $pres_res = Prescription::where(['customer_id' => $data['customer_id'], 'entry_code' =>  $data['entry_code']])->get();
            POSOrder::where(['order_no' => $data['entry_code']])->delete();
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
    function addPrescription(PrescriptionRequest $request)
    {
        Prescription::updateOrCreate([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,
            'status' => 'pending'
        ], $request->validated() + Parent::user_branch_id());
        $res = Prescription::where(['customer_id' => $request->customer_id, 'status' => 'pending'])->get();
        return response()->json($res);
    }
    function updatePrescription(PrescriptionRequest $request)
    {
        Prescription::updateOrCreate([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,
            'entry_code' => $request->entry_code
        ], $request->validated() + ['status' => 'completed'] + Parent::user_branch_id());
        $res = Prescription::where(['customer_id' => $request->customer_id, 'entry_code' => $request->entry_code, 'status' => 'completed'])->get();
        return response()->json($res);
    }

    function fetchPendingDescription()
    {
        $res = Prescription::where(['customer_id' => request('customer_id'), 'status' => 'pending'])->get();
        return $res;
    }
    function fetchPrescription()
    {
        $res = Prescription::fetchPrescription(request('entry_code'), request('customer_id'));
        return $res;
    }
    function fetchMedicalHistory()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = PatientMedicalRecord::with(['customer','user'])->where(['customer_id' => request('customer_id')])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('pre_existing_medical_conditions', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('allergies', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('past_surgeries_medical_procedures', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('family_medical_history', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('current_medication', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('blood_test', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('other_test_results', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('visiting_health_provider', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('purpose_of_visit', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('treatment_plans', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('healthcare_provider_report', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('symptoms_complaints', 'LIKE', '%' . request('search') . '%')

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

    function destroy(Request $request)
    {
        $id = $request->route('id');
        Prescription::where(['id' => $id])->delete();
        return true;
    }

    function deleteMedicalRecord(Request $request)
    {
        $entry_code = $request->entry_code;
        $this->deleteImageFiles($entry_code);
        Prescription::where(['entry_code' => $entry_code])->delete();
        PatientMedicalRecord::where(['entry_code' => $entry_code])->delete();

        return true;
    }
    public function deleteImageFiles($entry_code)
    {
        $item = PatientMedicalRecord::where(['entry_code' => $entry_code])->first();

        $file = $item->imaging_results;
        if ($file) {
            $file_path = public_path() . '/upload/' . $file;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    function fetchPatient()
    {
        $customer_id = request('customer_id');
        $res = Customer::where('id', $customer_id)->first();
        return $res;
    }
    function entryCode()
    {


        $latest = Prescription::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'PRE' . sprintf('%04d', $string);
        $isUnique = Prescription::where('entry_code', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'PRE' . ($latest->id + 1);
        }
        return  $orderNo;
    }

    function fetchPatientMedicalHistory(){
        $res= PatientMedicalRecord::where(['customer_id' => request('customer_id')])->orderBy('id','DESC')->first();
        return response()->json($res);
    }
}
