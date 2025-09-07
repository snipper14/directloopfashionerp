<?php

namespace App\Http\Controllers\Delivery;

use Carbon\Carbon;
use App\Invoices\Invoice;
use App\Delivery\Delivery;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Delivery\DeliveryRequest;

class DeliveryController extends BaseController
{
    public function create(DeliveryRequest $request)
    {
        $data_array = $request->data_array;
        $delivery_no = $this->deliveryNumber();
        
        foreach ($data_array as  $value) {
            Delivery::create([
                'delivery_no' => $delivery_no,
                'qty' => $value['qty'],
                'delivery_date' => $request->delivery_date,
               
                'stock_id' => $value['stock_id'],
                'customer_id' => $request->customer_id,
               
                'invoice_no' => $request->invoice_no,
                'invoice_id'=>$value['id']
            ]+Parent::user_branch_id());
        }
        $data = Delivery::with(['stock', 'customer','branch'])->where(['delivery_no'=>$delivery_no,'branch_id'=>Parent::branch_id()])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $delivery_no . '.pdf';
        $pdf = PDF::loadView('pdf.delivery_customer', compact('data'));

        $pdf->save($path);

        return response()->download($path);
    }
    function fetchByDnote(){
        
        $data = Delivery::with(['invoice','stock', 'customer','branch','user'])->where('delivery_no',  request('delivery_no'))->get();
       return $data;
    }
    public function  fetchByInvoice(Request $request)
    {
        $invoice_no = $request->invoice_no;
        $data = Invoice::with(['stock'])->where(['invoice_no'=>$invoice_no])->get();
        return response()->json(["results" => $data]);
}
    public function invoiceDeliveryNotes(Request $request)
    {
        //  DB::enableQueryLog();
        $data = Delivery::with(['stock', 'customer',"user"])->where([
            'customer_id' => $request->customer_id,
            'invoice_no' => $request->invoice_no
        ])->groupBy('delivery_no')->orderBy('id','DESC')->selectRaw('*,sum(qty) as qty_total')->get();
          //    print_r(DB::getQueryLog());
        return response()->json(['results'=>$data]);
    }

    public function downLoadNote(Request $request){
       $delivery_no=$request->route('delivery_no');
        $data = Delivery::with(['stock', 'customer','branch'])->where('delivery_no',  $delivery_no)->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $delivery_no . '.pdf';
        $pdf = PDF::loadView('pdf.delivery_customer', compact('data'));

        $pdf->save($path);

        return response()->download($path);
    
    }
    public function destroy(Request $request){
        $delivery_no=$request->route('delivery_no');
        Delivery::where('delivery_no',$delivery_no)->delete();
        return response()->json(['results' => 'deleted']);
    }
 
    function deliveryNumbser()
    {


        $latest = Delivery::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $deliveryNo = $today . '-' . 'DN' . sprintf('%04d', $string);
        $isUnique = Delivery::where('delivery_no', $deliveryNo)->first();
        if ($isUnique) {
            $deliveryNo = $today . '-' . 'DN' . ($latest->id + 1);
        }
        return  $deliveryNo.Parent::user_id();
    }
    function deliveryNumber()
    {
        
            $delivery_no = 'DN1';
        
            // Fetch the latest delivery without a hyphen ('-')
            $res = Delivery::where('delivery_no', 'NOT LIKE', '%-%')
                ->orderBy('id', 'DESC')
                ->first();
        
            if ($res) {
                $prev_delivery_no = $res->delivery_no;
        
                // Remove the "DN" prefix
                $text_to_remove = "DN";
                $delivery_no_sanitized = str_replace($text_to_remove, "", $prev_delivery_no);
        
                // Convert to integer and increment
                $current_delivery_no = (int) $delivery_no_sanitized + 1;
        
                // Construct new delivery number
                $delivery_no = 'DN' . $current_delivery_no;
            }
        
            return $delivery_no;
        
        
    }
}
