<?php

namespace App\Http\Controllers\Digitax;

use App\Customer\Customer;
use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class CustomerController extends BaseController
{


    public function addCustomerEtims(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'pin' => 'required',
        ]);
        try {
            $url = Parent::digitaxBaseUrl() . 'branches/customer';

            $data = [
                "customer_name" => $request->company_name,
                "customer_pin" => $request->pin,

            ];



            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();

            Customer::where(['id' => $request->id])->update([
                'etims_id' => $responseData['id'],
                'pin' => $request->pin

            ]);

            $data =   Customer::with('price_group')->where(['id' => $request->id])->first();
            if ($statusCode == 200) {
                return response()->json($data);
            } else {
                return response()->json(['status' => "error", "body" => "Etims Server  error"], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   
}
