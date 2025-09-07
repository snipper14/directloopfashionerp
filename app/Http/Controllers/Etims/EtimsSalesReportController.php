<?php

namespace App\Http\Controllers\Etims;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BaseController;

class EtimsSalesReportController extends BaseController
{
    function fetchDirectFromEtims(){
        try {
            $url = Parent::digitaxBaseUrl() . 'sales?page='.request('page').'$limit=30';

       


            $response = Http::withHeaders(Parent::apiHeaders())->get($url);


            $responseData = $response->json();


            $statusCode = $response->status();



      

            if ($statusCode == 200) {
                return response()->json($responseData);
            } else {
                return response()->json(['status' => "error", "body" => "Etims Server  error"], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
