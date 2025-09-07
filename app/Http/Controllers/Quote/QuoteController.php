<?php

namespace App\Http\Controllers\Quote;

use App\dept\Department;
use Carbon\Carbon;
use App\Quote\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Quote\QuoteRequest;
use Barryvdh\DomPDF\Facade as PDF;

class QuoteController extends BaseController
{
    public function create(QuoteRequest $request)
    {
        $department_id = Department::first()->id;
        Quotation::updateOrCreate(
            [
                'stock_id' => $request->stock_id,
                'customer_id' => $request->customer_id,
                'branch_id' => Parent::branch_id()
            ],
            [
                'product_name' => $request->name,
                'order_no' => $request->order_no,
                'user_id' => Parent::user_id(),
                'department_id' => $department_id,
                'order_date' => $request->order_date,
                'selling_price' => $request->selling_price,
                'discount' => $request->discount,
                'qty' => DB::raw('qty + ' . $request->qty),
                'row_total' => DB::raw('row_total + ' . $request->row_total),
                'tax_amount' => DB::raw('tax_amount + ' . $request->tax_amount)
            ]
        );



        $data = Quotation::with(['stock',  'customer'])
            ->where(['customer_id' => $request->customer_id, 'status' => 'pending'])->orderBy('id', 'DESC')->get();
        return $data;
    }

    public function orders(Request $request)
    {
        $data = Quotation::with(['stock',  'customer'])
            ->where(['customer_id' => $request->customer_id, 'status' => 'pending'])->orderBy('id', 'DESC')->get();
        return response()->json(["results" => $data]);
    }
    public function fetchCompleteQuoteByQuoteNo(Request $request)
    {
        $data = Quotation::with(['stock',  'customer'])
            ->where(['order_no' => $request->order_no, 'status' => 'complete'])->orderBy('id', 'DESC')->get();
        return response()->json($data);
    }
    function orderNumber(Request $request)
    {
        $id = $request->route('id');
        $res = Quotation::where(['customer_id' => $id, 'status' => 'pending'])->first();
        if ($res) {
            return response()->json(['result' => $res->order_no]);
        }
        $latest = Quotation::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'QTN' . sprintf('%04d', $string);
        $isUnique = Quotation::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'QTN' . ((int)$latest->id + 1);
        }
        return  response()->json(['result' => $orderNo . Parent::user_id()]);
    }
    function finalQuoteNo(){
        $latestQuotation = Quotation::where('order_no', 'NOT LIKE', '%QTN%')
    ->latest('id')
    ->first();
$quote_no=1;
    if($latestQuotation){
      $quote_no=  $latestQuotation->order_no+1;
    }
    return $quote_no;
    }
    public function cancelOrders(Request $request)
    {
        Quotation::where(['order_no' => $request->order_no])->delete();
        return true;
    }
    public function destroy(Request $request)
    {

        $order_no = $request->id;
        $order_no = Quotation::find($order_no);
        $data = $order_no->delete();

        return true;
    }
    function completeOrder(Request $request)
    {
        Quotation::where(['order_no' => $request->order_no])->update(['status' => 'complete','order_no'=>$this->finalQuoteNo()]);
    }

    public function fetch()
    {
        $data = Quotation::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('customer', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['stock', 'customer','user'])->where(['status' => 'complete'])->groupBy('order_no')->where(['branch_id' => Parent::branch_id()])->selectRaw('*, sum(qty) as sum_qty,sum(row_total) as sum_row_total')->latest()->paginate(30);

        return response()->json($data);
    }
    public function generateQuote(Request $request)
    {
        $order_no = $request->route('order_no');
        $data = Quotation::with(['stock',  'customer', 'branch', 'user'])->where('order_no', $order_no)->get();
        $folderPath = public_path('pdf');

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $order_no . '.pdf';

        $pdf = PDF::loadView('pdf.quotation', compact('data'));
        $pdf->save($path);
        return response()->download($path);
    }
    function getPrintData()
    {
        $data = Quotation::with(['stock',  'customer', 'branch', 'user'])->where('order_no', request('order_no'))->get();
        return $data;
    }
    function updateQuote(Request $request)
    {
        Quotation::where([
            'stock_id' => $request->stock_id,
            'customer_id' => $request->customer_id,
            'order_no' => $request->order_no
        ])->update(array(
            'qty' => DB::raw('qty + ' . $request->qty),
            'row_total' => DB::raw('row_total + ' . $request->row_total),
            'tax_amount' => DB::raw('tax_amount + ' . $request->tax_amount)
        ));
    }
}
