<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Options;
use App\Stock\Stock;
use Dompdf\FontMetrics;
use App\Customer\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    public function pgmBaseUrl()
    {
      // return "http://41.139.193.127:8888/api/v1/";
        // return "https://deitax.deitiestech.com/api/v1/";
        $url = 'http://197.232.146.218:8888/api/v1/';
        if (Auth::user()->branch->project_level == 'production') {
            $url = 'http://localhost:8888/api/v1/';
        }
        if (Auth::user()->branch->project_level == 'development') {
            $url =  'http://46.137.15.155/api/v1/';
        }
        // 
        return  $url;
        //  return 
    }

    public function pgmApiHeaders()
    {
        $username = 'admin';
        $password = 'admin';
        $credentials = base64_encode("{$username}:{$password}");
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $credentials,
        ];
        return $headers;
    }
    public function digitaxBaseUrl()
    {
        $url = "";
        if (Auth::user()->branch->project_level == 'production') {
            $url = 'https://api.digitax.tech/';
          //  $url = 'https://api.digitax.tech/ke/v2/';
        }
        return $url;
    }
    public function digitaxApiKey()
    {

        return Auth::user()->branch->etims_apikey;
    }
    public function digitaxApiKeytest()
    {
        return 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZXMiOlsiY2FuLWxvb2t1cC1jb25maWciLCJjYW4tbG9va3VwLWNvZGVzIiwiY2FuLWxvb2t1cC1pdGVtLWNsYXNzaWZpY2F0aW9ucyIsImNhbi1sb29rdXAtY3VzdG9tZXIiLCJjYW4tbG9va3VwLWJyYW5jaCIsImNhbi1sb29rdXAtbm90aWNlcyIsImNhbi1hZGQtYnJhbmNoLWN1c3RvbWVyIiwiY2FuLWFkZC1icmFuY2gtdXNlciIsImNhbi1sb29rdXAtcHJvZHVjdCIsImNhbi1hZGQtc2FsZXMiLCJjYW4tYWRkLXN0b2NrIiwiY2FuLWxvb2t1cC1zdG9jayIsImNhbi1hZGQtcHJvZHVjdCIsImNhbi1hZGQtbWFzdGVyLXN0b2NrIiwiY2FuLWxvb2t1cC1wdXJjaGFzZXMiLCJjYW4tYWRkLXB1cmNoYXNlcyIsImNhbi1sb29rdXAtaW1wb3J0cyIsImNhbi1yZXZpc2UtaW1wb3J0cyIsImNhbi1kZWxldGUtcHJvZHVjdCIsImNhbi11cGRhdGUtcHJvZHVjdCJdLCJhcHBJZCI6ImNsbm85czl6YjAyZnFzNjAxdTVpNzlhMDYiLCJpYXQiOjE3MDI2NjE0MjAsImV4cCI6MjAxODAyMTQyMH0.DpV-rBYfMzWJSjkzoVLlVXi5pr39qw7BfEP-MT2Hzg0';
    }
    public function apiHeaders()
    {
        $headers = [
            'X-API-Key' => $this->digitaxApiKey(),
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
            // Add other headers as needed
        ];
        return $headers;
    }
    function generateDocNumber($prefix)
    {
        // Generate a unique identifier based on the current time
        $uniqueId = uniqid();

        // Create a timestamp (you can customize the format as needed)
        $timestamp = date('YmdHis');

        // Combine the prefix, timestamp, and unique identifier
        $invoiceNumber = $prefix . $timestamp . $uniqueId;

        return $invoiceNumber;
    }
    function removeSecondName($string)
    {

        $words = explode(' ', $string);

        if (count($words) > 1) {
            array_splice($words, 1, 1); // Remove the second word
            $result = implode(' ', $words);
        } else {
            $result = $string; // No second word to remove
        }

        return $result;
    }
    public function product_category_id($stock_id)
    {
        $res = Stock::where("id", $stock_id)->first();
        if ($res) {
            return $res->product_category_id;
        } else {
            return "";
        }
    }
    public function user_id()
    {
        return Auth::user()->id;
    }
    public function branch_id()
    {
        return Auth::user()->branch_id;
    }
    public function user_branch_id()
    {
        return ["user_id" => $this->user_id(), 'branch_id' => $this->branch_id()];
    }
    public function branch_array()
    {
        return ['branch_id' => $this->branch_id()];
    }
    public function last_query()
    {

        DB::enableQueryLog(); // Enable query log

        return DB::getQueryLog(); // Show results of log
    }
    public function checkForPermission()
    {

        $user = Auth::user();

        $permission = json_decode($user->role->permission);

        $hasPermission = false;

        if (!$permission) {

            return  $hasPermission;
        }

        foreach ($permission as $p) {

            foreach ($p->children as $c) {

                if ($c->name == request('currentRoute')) {

                    if ($c->fetch) {

                        $hasPermission = true;
                    } else {
                    }
                }
            }
        }
        return $hasPermission;
    }

    public function showBranchPermission()
    {

        $user = Auth::user();

        $permission = json_decode($user->role->permission);

        $hasPermission = false;

        if (!$permission) {

            return  $hasPermission;
        }

        foreach ($permission as $p) {

            foreach ($p->children as $c) {

                if ($c->name == request('currentRoute')) {

                    if ($c->branch) {

                        $hasPermission = true;
                    } else {
                    }
                }
            }
        }
        return $hasPermission;
    }

    function currentDateTime()
    {
        $currentTime = Carbon::now();
        return $currentTime->toDateTimeString();
    }
    function currentDate()
    {
        $ldate = date('Y-m-d');
        return $ldate;
    }
    function customerCode()
    {
        $latest = Customer::latest()->first();
        if ($latest) {
            $productCode = ($latest->id + 1);

            $isUnique = Customer::where('acc_code', $productCode)->first();
            if ($isUnique) {

                $productCode = $this->uniqueCode($productCode);
            }
            return  $productCode;
        } else {
            return 1;
        }
    }
    function uniqueCode($code)
    {

        $productCode =  $code + 1;
        $isUnique = Customer::where('acc_code', $productCode)->first();
        if ($isUnique) {
            $productCode = $this->uniqueCode($productCode);
        }
        return $productCode;
    }
    function getUnitId($stock_id)
    {
        return   Stock::where(['id' => $stock_id])->first()->unit_id;
    }
    function isInventory($stock_id)
    {
        $rs = Stock::withTrashed()->where('id', $stock_id)->first();
        if ($rs->inventory_type == "inventory") {

            return true;
        } else {

            return false;
        }
    }

    function calculateTax($tax_rate, $amount)
    {
        $tax_amount =
            $amount -
            $amount * (100 / (100 + $tax_rate));

        return $tax_amount;
    }

    function createWatermark($pdf)
    {
        $canvas = $pdf->getDomPDF()->getCanvas();
        // Instantiate font metrics class 
        $options = new Options();

        $options->set('isPhpEnabled', 'true');

        $fontMetrics = new FontMetrics($canvas, $options);

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        $font = $fontMetrics->getFont('times');

        $text = "CONFIDENTIAL";
        $txtHeight = $fontMetrics->getFontHeight($font, 75);
        $textWidth = $fontMetrics->getTextWidth($text, $font, 75);


        $x = (($w - $textWidth) / 2);
        $y = (($h - $txtHeight) / 2);



        $canvas->set_opacity(0.1, 'Multiply');
        $canvas->page_text(
            $x,
            $y,
            $text,
            $font,
            35,
            array(1, 0, 0),
            2,
            2,
            -30
        );
    }

    function uniqueID()
    {
        $uniqueId = Str::uuid();
        return $uniqueId;
    }
}
