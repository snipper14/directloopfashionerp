<?php

namespace App\Http\Controllers\Branch;

use App\Branch\Branch;
use App\dept\Department;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Branch\BranchRequest;
use App\Scopes\BranchScope;
use App\Stock\Stock;
use App\User;

class BranchController extends BaseController
{
    function create(Request $request)
    {
        $data = json_decode($request['data'], true);
        //  if($request->file!="null"){
        $request->validate([
            'file' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
        ]);
        //}
        $rules = [
            'branch' => "required|unique:branches,branch,NULL,id,deleted_at,NULL",
            'business_name' => 'required:max:50',
            'address' => 'required:max:50|required',
            'postal_address' => "required:max:30|required",
            'country' => "max:50|required",
            'town' => "max:50|required",
            'kra_pin' => 'max:50',
            'phone_no' => 'max:50|required',
            'till_no' => 'nullable',
            'email' => "max:200|required|email",
            'details' => "max:250",
            'kitchen_printer' => 'nullable',
            'bill_printer' => 'nullable',
            'invoice_printer' => 'nullable',
            'print_type' => 'required',
            'enforce_pos_qty' => 'required',
            'fisc_api' => "nullable",
            'cu_ip' => "nullable",
            'cu_port' => "nullable",
            'cu_password' => "nullable",
            'cu_serial' => "nullable",
            'account_id' => 'nullable',
            'mpesa_account_id' => "nullable",
            'etims_apikey' => 'nullable',
            'footer_text' => 'nullable',
            'min_profit_margin' => 'numeric|required',
            'payroll_account_id'=>'nullable',
             'logo_height'=>'nullable',
             'logo_width'=>'nullable',
             'strip_color'=>'nullable',
             'enforce_customer_retail'=>'required',
             'etims_type'=>'required',
             'print_copies' => 'numeric|required',
             'project_level' => 'required',
             'logo_type' => 'required',
              'cashier_options'=>"required",
              'selling_price_type'=>'required',
              

        ];
        $validator = Validator::make($data, $rules);
        if ($request->file != "null") {
            $upload_path = public_path('upload');
            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            Branch::create($validator->validated() + ['logo' => $generated_new_name]);
        } else {
            Branch::create($validator->validated());
        }

        $res = Branch::orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    public function update(Request $request)
    {

        $data = json_decode($request['data'], true);

        if ($request->file != 'null') {
            $request->validate([
                'file' => 'mimes:jpeg,jpg,png,gif|max:10000'
            ]);
        }

        $rules = [
            'loyalty_points_rate'=>'required|numeric',
            'id' => 'required',
            'branch' => "required|unique:branches,branch,{$data['id']},id,deleted_at,NULL",
            'business_name' => 'required:max:50',
            'address' => 'required:max:50',
            'postal_address' => "required:max:30",
            'country' => "max:50|required",
            'town' => "max:50|required",
            'kra_pin' => 'max:50',
            'phone_no' => 'max:50|required',
            'till_no' => 'nullable',
            'email' => "max:200|email|required",
            'details' => "max:250",
            'kitchen_printer' => 'nullable',
            'bill_printer' => 'nullable',
            'invoice_printer' => 'nullable',
            'print_type' => 'required',
            'enforce_pos_qty' => 'required',
            'account_id' => 'nullable',
            'mpesa_account_id' => 'nullable',
            'etims_apikey' => 'nullable',
            'fisc_api' => "nullable",
            'cu_ip' => "nullable",
            'cu_port' => "nullable",
            'cu_password' => "nullable",
            'cu_serial' => "nullable",
            'footer_text' => 'nullable',
            'min_profit_margin' => 'numeric|required',
            'payroll_account_id'=>'nullable',
            'logo_height'=>'nullable',
            'logo_width'=>'nullable',
            'strip_color'=>'nullable',
            'enforce_customer_retail'=>'required',
             'etims_type'=>'required',
             'print_copies' => 'numeric|required',
             'project_level' => 'required',
             'logo_type' => 'required',
              'cashier_options'=>"required",
              'selling_price_type'=>'required',
              'header_height'=>"nullable",
            'is_discount_allowed' => 'required|in:yes,no', // New validation rule for is_discount_allowed
        ];
       

        $validator = Validator::make($data, $rules);


        if ($request->file != 'null') {
            $upload_path = public_path('upload');


            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            $this->deleteLogoImageFiles($data['id']);
            Branch::where('id', $data['id'])->update($validator->validated() + ['logo' => $generated_new_name]);
        } else {
            Branch::where('id', $data['id'])->update($validator->validated());
        }
        $res = Branch::orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    public function deleteLogoImageFiles($id)
    {
        $item = Branch::find($id);
        $file = $item->logo;
        if ($file) {


            $file_path = public_path() . '/upload/' . $file;

            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $this->deleteLogoImageFiles($id);
        Branch::find($id)->delete();
        return response()->json(['message' => "record deleted"]);
    }

    public function fetch()
    {
        $res = Branch::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('branch', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
    function visitBranch(Request $request)
    {
        $user_id = Parent::user_id();
        $department = Department::withoutGlobalScope(BranchScope::class)->where(['branch_id' => $request->branch_id])->first();
        if (!$department) {
            $department = Department::create(['branch_id' => $request->branch_id, 'department' => $request->branch, 'user_id' => $user_id]);
        }
        User::where(['id' => $user_id])->update(['branch_id' => $request->branch_id, 'department_id' => $department->id]);
        return true;
    }
}
