<?php

namespace App\Http\Controllers\DirectRetailsCredit;

use Carbon\Carbon;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\CreditNote\DirectCreditNote;
use App\Http\Controllers\Controller;
use function PHPUnit\Framework\isEmpty;
use App\Http\Controllers\BaseController;

use Illuminate\Support\Facades\Validator;
use App\DirectRetailCredit\DirectRetailsCredit;
use App\Http\Requests\DirectRetailsCredit\DirectRetailsCreditRequest;

class DirectRetailsCreditController extends BaseController
{
    function create(DirectRetailsCreditRequest $request)
    {
        DirectRetailsCredit::updateOrCreate(
            ['stock_id' => $request->stock_id, 'status' => 'progress', 'customer_id' => $request->customer_id],
            $request->validated() + Parent::user_branch_id()
        );

        return $this->PendingQuery($request->customer_id);
    }
    function createReplacement(DirectRetailsCreditRequest $request)
    {
        DirectRetailsCredit::updateOrCreate(
            ['stock_id' => $request->stock_id, 'status' => 'progress', 'customer_id' => $request->customer_id],
            $request->validated() + ['entry_type' => $request->entry_type] + Parent::user_branch_id()
        );

        return $this->PendingQuery($request->customer_id, $request->credit_no);
    }
    function PendingQuery($customer_id, $credit_no = null)
    {
        $res = DirectRetailsCredit::with(['stock', 'customer'])->where(['status' => 'progress', "customer_id" => $customer_id])

            ->when($credit_no, function ($query, $credit_no) {
                $query->where('credit_no', $credit_no);
            })->latest()->get();
        return $res;
    }
    function completeCreditNote(Request $request)
    {
        $data = json_decode($request['data'], true);
        //if ($request->file != "null") {
        $request->validate([
            'file' => 'mimes:jpeg,jpg,png,webp,gif,pdf|required|max:10000'
        ]);
        //  }
        $rules = [

            'credit_no' => "required",
            'customer_id' => 'required'

        ];
        $ref_no = $this->getRefNo();
        $customMessages = [];
        $validator = Validator::make($data, $rules, $customMessages);
        if ($request->file != "null") {

            $upload_path = public_path('uploads');

            if (!file_exists($upload_path)) {
                mkdir($upload_path);
            }
            $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move($upload_path, $generated_new_name);
            DirectRetailsCredit::where(['credit_no' => $data['credit_no'], 'customer_id' => $data['customer_id']])->update(
                ['ref_no' =>  $ref_no, 'image' => $generated_new_name, 'status' => 'completed']
            );
        } else {
            DirectRetailsCredit::where(['credit_no' => $data['credit_no'], 'customer_id' => $data['customer_id']])->update(['ref_no' =>  $ref_no, 'status' => 'completed']);
        }
       $data_input= DirectRetailsCredit::where(['ref_no' =>  $ref_no])->get();
        if(count($data_input)>0){
           
            foreach ($data_input as  $value) {
                # code...
            
            if (Parent::isInventory($value['stock_id'])) {
                $loc_query = Department::where('show_pos', 1)->first();
                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $loc_query->id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $value['qty'])]
                );
            }}
        }
        $res =  DirectRetailsCredit::with(['stock', 'customer'])->where(['ref_no' =>  $ref_no, "customer_id" => $data['customer_id']])->latest()->get();
        return $res;
    }
    function getByCreditNo()
    {
        $res =  DirectRetailsCredit::with(['stock', 'customer'])->where(['customer_id' => request('customer_id'), 'credit_no' => request('credit_no')])->latest()->get();
        return $res;
    }
    function fetchCreditByCreditNo()
    {
        $res =  DirectRetailsCredit::with(['stock', 'customer'])->where(['customer_id' => request('customer_id'), 'credit_no' => request('credit_no'), 'entry_type' => 'original'])->latest()->get();
        return $res;
    }
    function fetchReplacement()
    {
        $res =  DirectRetailsCredit::with(['stock', 'customer'])->where(['customer_id' => request('customer_id'), 'credit_no' => request('credit_no'), 'status' => 'completed', 'entry_type' => 'replacement'])->latest()->get();
        return $res;
    }
    function getRefNo()
    {

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderno = $today . '-' . 'CR' . sprintf('%04d', $string);
        return $orderno;
    }
    function fetchProgress()
    {
        return $this->PendingQuery(request('customer_id'));
    }
    function fetchReplacementProgress()
    {
        return $this->PendingQuery(request('customer_id'), request('credit_no'));
    }
    function destroy(Request $request)
    {
        $id = $request->id;
        DirectRetailsCredit::bulkDelete(['id' => $id]);
        return true;
    }
    function groupByCreditNo()
    {
        $row_query = DirectRetailsCredit::with(['stock', 'customer', 'branch', 'user']);
        $row_query->when(request('search', '') != '', function ($query) {

            $query->where('credit_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('ref_no', 'LIKE', '%' . request('search') . '%')

                ->orWhere('customer_name', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' .  request('search') . '%')
                        ->orWhere('company_phone', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' .  request('search') . '%');
                })
                ->orWhereHas('branch', function ($q) {
                    $q->where('branch', 'LIKE', '%' .  request('search') . '%');
                });
        });
        $row_query->selectRaw("*,SUM(qty) AS sum_qty,SUM(row_total) AS sum_row_amount,SUM(row_vat) AS sum_row_vat");
        if (!Parent::checkForPermission()) {
            $row_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            // $row_query->withoutGlobalScope(BranchScope::class);
        }
        if (request('status') == "completed") {
            $row_query->where('status', 'completed');
        }

        $row_query->groupBy(['credit_no', 'customer_id'])->latest();
        $res = $row_query->get();
        if (request('page') > 0) {
            $res = $row_query->paginate(30);
        }
        return response()->json($res);
    }
    function completeExchangeMatch(Request $request)
    {
        $ref_no = $this->getRefNo();
        for ($i = 0; $i < count($request->data_array); $i++) {
            $value = $request->data_array[$i];
            DirectRetailsCredit::where(['id' => $value['id']])->update(['status' => 'completed', 'ref_no' => $ref_no]);
            if (Parent::isInventory($value['stock_id'])) {
                $loc_query = Department::where('show_pos', 1)->first();
                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $loc_query->id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty -' . $value['qty'])]
                );
            }
        }

        return true;
    }
    function deleteCreditNote(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res =  DirectRetailsCredit::where(['customer_id' => $request->customer_id, 'credit_no' => $request->credit_no])->first();
            $reduce_inventory =  DirectRetailsCredit::where(['entry_type'=>'original','customer_id' => $request->customer_id, 'credit_no' => $request->credit_no])->get();
            if(count($reduce_inventory)>0){
               
                foreach ($reduce_inventory as  $value) {
                    # code...
                
                if (Parent::isInventory($value['stock_id'])) {
                    $loc_query = Department::where('show_pos', 1)->first();
                    Inventory::updateOrCreate(
                        ['stock_id' => $value['stock_id'], 'department_id' => $loc_query->id, 'branch_id' => Parent::branch_id()],
                        ['qty' => DB::raw('qty -' . $value['qty'])]
                    );
                }}
            }
            if (is_null($res->unique_code)) {
                DirectRetailsCredit::bulkDelete(['customer_id' => $request->customer_id, 'credit_no' => $request->credit_no]);
            }
        $sale=    AllSaleDetailsReport::where(['order_no' => $res->receipt_no])->get();
   
        if(count($sale)>0){
           
            foreach ($sale as  $value) {
                # code...
            
            if (Parent::isInventory($value['stock_id'])) {
            
                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $value['department_id'], 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $value['qty'])]
                );
            }}
        }else{
            $increase_inventory =  DirectRetailsCredit::where(['entry_type'=>'replacement','customer_id' => $request->customer_id, 'credit_no' => $request->credit_no])->get();
            if(count($increase_inventory)>0){
               
                foreach ($increase_inventory as  $value) {
                    # code...
                
                if (Parent::isInventory($value['stock_id'])) {
                    $loc_query = Department::where('show_pos', 1)->first();
                    Inventory::updateOrCreate(
                        ['stock_id' => $value['stock_id'], 'department_id' => $loc_query->id, 'branch_id' => Parent::branch_id()],
                        ['qty' => DB::raw('qty +' . $value['qty'])]
                    );
                }}
            }
        }
      
            GeneralLedger::bulkDelete(['unique_code' => $res->unique_code]);
            AllSalesTotalReport::bulkDelete(['receipt_no' => $res->receipt_no]);
            AllSaleDetailsReport::bulkDelete(['order_no' => $res->receipt_no]);
            DirectRetailsCredit::bulkDelete(['customer_id' => $request->customer_id, 'credit_no' => $request->credit_no]);
            $file = $res->image;
            if ($file) {
                $file_path = public_path() . '/uploads/' . $file;

                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }, 5);
        return true;
    }
}
