<?php

namespace App\Http\Controllers\Sickoff;

use Carbon\Carbon;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\Medical\Sickoff;
use App\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Sickoff\SickoffRequest;
use Illuminate\Database\Console\Migrations\BaseCommand;

class SickoffController extends BaseController
{
    //
    function create(SickoffRequest $request)
    {
        $res=null;
        DB::transaction(function () use ($request,&$res) {
        $entry_code=$this->entryCode();
        $res =  Sickoff::create($request->validated()+['entry_code'=>$entry_code] + Parent::user_branch_id());


        $stock=Stock::with(['tax_dept'])->where(['product_name'=>'SICKOFF'])->first();
        
        if($stock){
            $customer=Customer::find($request->customer_id);
      
            $tax_rate=$stock->tax_dept->tax_rate;
             $row_amount=$request->cost;
              $row_tax=Parent::calculateTax($tax_rate,$row_amount);
              POSOrder::create(
                  [
                      'stock_id' => $stock->id,
                     
                      'order_date' => Parent::currentDate(),
                      'qty' => 1,
                      'selling_price' =>$request->cost,
                      'row_amount' =>($row_amount),
                      'row_vat' =>$row_tax,
                      'order_no' =>$entry_code,
                      'tax_rate' => $tax_rate,
                      'product_name' => $stock->product_name,
                      'code' => $stock->code,
                      'batch_no' => '',
                      'customer_id' => $request->customer_id,
                      'customer_name' => $customer->company_name,
      
      
                  ]
                      + Parent::user_branch_id()
              );
        }
    },5);
        return response()->json($res);
    }
    function entryCode()
    {


        $latest = Sickoff::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'SO' . sprintf('%04d', $string);
        $isUnique = Sickoff::where('entry_code', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'SO' . ($latest->id + 1);
        }
        return  $orderNo;
    }
    function fetch()
    {
        $main_query = Sickoff::with(['customer', 'user'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {

                    $q->where('patient_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('doctors_report', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('doctors_name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('reg_no', 'LIKE', '%' . request('search') . '%')

                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->where(['customer_id'=>request('customer_id')])->orderBy('id', 'DESC');

        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return response()->json($data);
    }

    function destroy(Request $request){
        $id=$request->route('id');
        Sickoff::find($id)->delete();
        return true;
    }
}
