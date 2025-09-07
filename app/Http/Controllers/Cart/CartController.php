<?php

namespace App\Http\Controllers\Cart;

use App\Sale\Sale;
use App\Stock\Stock;
use App\Cart\OrderCart;
use App\Customer\Customer;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Rules\OrderAvailability;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateBatchNoInvoice;
use App\Rules\ValidateBatchQtyInvoice;
use App\Http\Requests\Cart\CartRequest;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateBatchExpiryInvoice;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateCustomerCreditLimit;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class CartController extends BaseController
{

    public function create(CartRequest $request)
    {
        $validator = Validator::validate($request->all(), [
            'customer_id' => new ValidateCustomerCreditLimit($request->all()),
  
    
  
          ]);
        DB::transaction(function () use ($request) {
            $this->validate(
                $request,
                [
                    'qty' => new OrderAvailability($request->stock_id, $request->department_id),
                    'stock_id' => [new ValidateBatchNoInvoice($request), new ValidateBatchExpiryInvoice($request), new ValidateBatchQtyInvoice($request)],
                ]
            );
            $rs = Stock::where('id', $request->stock_id)->first();
            if ($request->batch_no) {
                PurchaseOrderReceivableWithBatch::where(['stock_id' => $request->stock_id, 'batch_no' => $request->batch_no])->increment('qty_sold', $request->qty);
            }
            OrderCart::updateOrCreate(
                [
                    'stock_id' => $request->stock_id, 'customer_id' => $request->customer_id,
                    'branch_id' => Parent::branch_id()
                ],
                [
                    'order_no' => $request->order_no,
                    'user_id' => Parent::user_id(),
                    'department_id' => $request->department_id,
                    'product_category_id' => $request->product_category_id,
                    'order_date' => $request->order_date,
                    'selling_price' => $request->selling_price,
                    'purchase_price' => $request->purchase_price,
                    'discount' => $request->discount,
                    'qty' => DB::raw('qty + ' . $request->qty),
                    'row_total' => DB::raw('row_total + ' . $request->row_total),
                    'tax_amount' => DB::raw('tax_amount + ' . $request->tax_amount),
                    'batch_no' => $request->batch_no
                ]
            );
            if ($rs->inventory_type == "inventory") {

                Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->decrement('qty', $request->qty);
            }
        }, 5);
        return true;
    }
    public function orders(Request $request)
    {
        $data = OrderCart::with(['stock',  'customer'])
            ->where(['customer_id' => $request->customer_id])->orderBy('id', 'DESC')->get();
        return response()->json(["results" => $data]);
    }

    public function update(CartRequest $request)
    {
        // $data = OrderCart::create($request->validated());
        // return true;
    }


    public function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            $order_no = $request->id;
            $order_no = OrderCart::find($order_no);
            $data = $order_no->delete();
            if (Parent::isInventory($request->stock_id)) {
                Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->increment('qty', $request->qty);
            }
            if ($request->batch_no) {
                PurchaseOrderReceivableWithBatch::where(['stock_id' => $request->stock_id, 'batch_no' => $request->batch_no])->decrement('qty_sold', $request->qty);
            }
        }, 5);
        return true;
    }
    public function cancelOrders(Request $request)
    {
        $results = OrderCart::where('customer_id', $request->id)->get();
        if (!$results->isEmpty()) {

            foreach ($results as $value) {
                $id = $value['id'];
                OrderCart::find($id)->delete();
                if (Parent::isInventory($value['stock_id'])) {


                    Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $value['department_id']])->increment('qty', $value['qty']);
                }
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->decrement('qty_sold', $value['qty']);
                }
            }
            return true;
        } else {

            return true;
        }
    }
    public function recallOrder(Request $request)
    {
        DB::transaction(function () use ($request) {
            $sale_res = Sale::where('order_no', $request->order_no)->get();
            foreach ($sale_res as  $value) {
                OrderCart::create(
                    [
                        'order_no' => $value['order_no'],
                        'stock_id' => $value['stock_id'],
                        'department_id' => $value['department_id'],
                        'customer_id' => $value['customer_id'],
                        'user_id' => $value['user_id'],
                        'branch_id' => $value['branch_id'],
                        'selling_price' => $value['item_price'],
                        'row_total' => $value['row_total'],
                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'tax_amount' => $value['tax_amount'],
                        'purchase_price' => $value['purchase_price'],
                        'discount' => $value['discount'],
                        'product_category_id' => $value['product_category_id'],
                        'batch_no' => $value['batch_no']


                    ]
                );
            }
            Sale::where('order_no', $request->order_no)->delete();
        }, 5);
        $customer = Customer::where('id', $request->customer_id)->get();
        return response()->json($customer);
    }
}
