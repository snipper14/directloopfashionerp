<?php

namespace App\Http\Controllers\PreOrders;

use Carbon\Carbon;
use App\PreOrder\Preorder;
use App\Scopes\BranchScope;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Sale\AllSalesTotalReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidatePOSCreditLimit;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;
use App\Http\Requests\PreOrders\PreorderRequest;
use App\Ledgers\GeneralLedger;
use App\Sale\AllSaleDetailsReport;

class PreorderControllers extends BaseController
{




    function create(PreorderRequest $request)
    {
        Preorder::updateOrCreate(
            ['stock_id' => $request->stock_id, 'order_status' => 'progress', 'customer_id' => $request->customer_id],
            $request->validated() + Parent::user_branch_id()
        );

        return $this->PendingQuery($request->customer_id);
    }
    function PendingQuery($customer_id)
    {
        $res = Preorder::with(['stock', 'customer'])->where(['order_status' => 'progress', "customer_id" => $customer_id])->latest()->get();
        return $res;
    }
    function fetchProgress()
    {
        return $this->PendingQuery(request('customer_id'));
    }
    function destroy(Request $request)
    {
        $id = $request->id;
        Preorder::bulkDelete(['id' => $id]);
        return true;
    }
    function getOrderNo()
    {


        $res = Preorder::where(['customer_id' => request('customer_id'), 'order_status' => 'progress'])->get();
        if (count($res) > 0) {
            return  $res;
        }


        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderno = $today . '-' . 'OR' . sprintf('%04d', $string);
        return $orderno;
    }
    function fetchGroupedPreorder()
    {
        $row_query = Preorder::with(['stock', 'customer', 'branch', 'user']);
        $row_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
            ->orWhere('customer_phone', 'LIKE', '%' . request('search') . '%')
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
            $row_query->withoutGlobalScope(BranchScope::class);
        }
        if (request('status') == "completed") {
            $row_query->where('order_status', 'completed');
        }
        if (request('status') == "ready") {
          $row_query->whereIn('order_status', ['ready', 'delivered']);
        }
        $row_query->groupBy('order_no')->latest();
        $res = $row_query->get();
        if (request('page') > 0) {
            $res = $row_query->paginate(30);
        }
        return response()->json($res);
    }

    function deleteOrder(Request $request) {
        DB::transaction(function () use ($request) {
        $order_no=$request->order_no;
           $orders= Preorder::where(['order_no'=>$order_no])->first();
        CustomerLedger::where('unique_code',$orders->unique_code)->delete();
        GeneralLedger::where('unique_code',$orders->unique_code)->delete();
       Preorder:: where('unique_code',$orders->unique_code)->delete();
       AllSalesTotalReport::where('order_no',$order_no)->delete();
       AllSaleDetailsReport::where('order_no',$order_no)->delete();
        },5);
    }

    function getByOrderNo()
    {
        $res = Preorder::with(['stock', 'customer'])->where([ "order_no" => request('order_no')])->latest()->get();
        $sales_datails=AllSalesTotalReport::with([ 'customer'])->where([ "order_no" => request('order_no')])->latest()->first();
        return response()->json(['sales_details'=>$sales_datails,'res'=>$res]);
    }
    function completeOrder(Request $request){
        Preorder::where('order_no',$request->order_no)->update(['order_status'=>'ready']);
        return true;
    }
    function completeDelivery(Request $request){
        Preorder::where('order_no',$request->order_no)->update(['order_status'=>'delivered']);
        return true;
    }
}
