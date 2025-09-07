<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PurchaseOrder\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\PurchaseOrder\PurchaseOrderTemp;
use App\Http\Requests\PurchaseOrder\PurchaseOrderRequestTemp;

class TempPurchaseOrderController extends BaseController
{
    function create(PurchaseOrderRequestTemp $request)
    {
        PurchaseOrderTemp::updateOrCreate(
            [
                'stock_id' => $request->stock_id, 'supplier_id' => $request->supplier_id,
                'branch_id' => Parent::branch_id()
            ],
            $request->validated() + Parent::user_branch_id()
        );

        return true;
    }

    function fetch()
    {
        $res = PurchaseOrderTemp::fetch();
        return $res;
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        PurchaseOrderTemp::where('id', $id)->delete();
        return true;
    }
    function completeOrder()
    {
        DB::transaction(function () {
            $res_grouped = PurchaseOrderTemp::fetchGrouped();
            foreach ($res_grouped as  $value1) {
                $res_grouped2 = PurchaseOrderTemp::where('supplier_id', $value1['supplier_id'])->get();
                $order_no = $this->orderNumber();
                foreach ($res_grouped2 as  $value) {
                    $insert_array = [
                        'supplier_id' => $value['supplier_id'],
                        'vendor_reference' => $value['vendor_reference'],
                        'order_no' => $order_no,
                        'order_deadline' => $value['order_deadline'],
                        'receipt_date' => $value['receipt_date'],
                        'stock_id' => $value['stock_id'],
                        'shipping_address' => $value['shipping_address'],
                        'qty' => $value['qty'],
                        'unit_price' => $value['unit_price'],
                        'sub_total' => $value['sub_total'],
                        'taxes' => $value['taxes'],
                        'tax_rate' => $value['tax_rate'],
                        'progress' => 'completed'
                    ];
                    PurchaseOrder::create($insert_array + [
                        'user_id' => Auth::user()->id,
                        'branch_id' => Auth::user()->branch_id,
                        'approver_id' => Auth::user()->id
                    ]);
                }
            }
        }, 5);
        PurchaseOrderTemp::truncate();
        return true;
    }
    function orderNumber()
    {


        $latest = PurchaseOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'PO' . sprintf('%04d', $string);
        $isUnique = PurchaseOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'PO' . ($latest->id + 1);
        }
        return  $orderNo;
    }
}
