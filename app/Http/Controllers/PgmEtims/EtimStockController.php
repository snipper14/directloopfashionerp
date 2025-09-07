<?php

namespace App\Http\Controllers\PgmEtims;

use Exception;
use App\Unit\Unit;
use App\Stock\Stock;
use App\tax\TaxDept;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\EtimStock\EtimsItemType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\EtimStock\EtimsOriginCountry;
use App\EtimStock\ItemClassification;
use App\Http\Controllers\BaseController;
use App\EtimStock\EtimsItemPackagingCode;

class EtimStockController extends BaseController
{

    function syncAllStockToEtims()
    { ini_set('max_execution_time', 2400);
        DB::transaction(function () {
            $stock = Stock::whereNull('etims_code')->limit(1000)->get();
            $url = Parent::pgmBaseUrl() . 'items';
          
                foreach ($stock as  $value) {
                    $itemClassification =  ItemClassification::first();
                    $etimsItemType = EtimsItemType::first();
                    $etimsOriginCountry = EtimsOriginCountry::first();
                    $etimsItemPackagingCode = EtimsItemPackagingCode::first();
                    $taxDept = TaxDept::find($value['tax_dept_id']);
                    $unit = Unit::find($value['unit_id']);
                    $data = [
                        "name" => $value['product_name'],
                        "orgCountryCode" => $etimsOriginCountry->code,
                        "unitPrice" => $value['selling_price'],
                        "itemTypeCode" => $etimsItemType->code,
                        "taxCode" => $taxDept->tax_code,
                        "qtyUnitCode" =>  $unit->code,
                        "pkgUnitCode" =>  $etimsItemPackagingCode->code,
                        "itemClassCode" => $itemClassification->code,
                        "initialStock" => 1000000.00,

                    ];
                    try {
                    $response = Http::withHeaders(Parent::pgmApiHeaders())->post($url, $data);
                    $responseData = $response->json();
                    $statusCode = $response->status();
                    if ($statusCode == 200) {
                        Stock::where(['id' => $value['id']])->update([
                            'etims_code' => $responseData['data']['itemCode'],
                            'item_classification_id' => $itemClassification->id,
                            'etims_item_type_id' => $etimsItemType->id,
                            'etims_origin_country_id' => $etimsOriginCountry->id,
                            'etims_item_packaging_code_id' => $etimsItemPackagingCode->id
                        ]);
                       // echo response()->json(json_encode($responseData));
                    } else {
                        //return;
                    }
                } catch (Exception $e) {

                    //return response()->json(['error' => $e->getMessage()], 500);
                }
                }
         
     }, 5);
    }
    public function addItems(Request $request)
    {
        $request->validate([
            'item_classification_id' => "required",
            'etims_item_type_id' => "required",
            'etims_origin_country_id' => "required",
            'etims_item_packaging_code_id' => "required",
            'id' => 'required',
            'current_qty' => "numeric|required"
        ]);
        try {
            $url = Parent::pgmBaseUrl() . 'items';
            $itemClassification =  ItemClassification::find($request->item_classification_id);
            $etimsItemType = EtimsItemType::find($request->etims_item_type_id);
            $etimsOriginCountry = EtimsOriginCountry::find($request->etims_origin_country_id);
            $etimsItemPackagingCode = EtimsItemPackagingCode::find($request->etims_item_packaging_code_id);
            $taxDept = TaxDept::find($request->tax_dept_id);

            if (!$taxDept) {
                return response()->json(['error' => "Tax code missing"], 403);
            }
            $unit = Unit::find($request->unit_id);
            if (!$unit) {
                return response()->json(['error' => "Uni code missing"], 403);
            }
            $stock = Stock::find($request->id);

            $data = [
                "name" => $stock->product_name,
                "orgCountryCode" => $etimsOriginCountry->code,
                "unitPrice" => $stock->selling_price,
                "itemTypeCode" => $etimsItemType->code,
                "taxCode" => $taxDept->tax_code,
                "qtyUnitCode" =>  $unit->code,
                "pkgUnitCode" =>  $etimsItemPackagingCode->code,
                "itemClassCode" => $itemClassification->code,
                "initialStock" => (float)$request->current_qty,







            ];


            $response = Http::withHeaders(Parent::pgmApiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();
         if ($statusCode == 200) {
                Stock::where(['id' => $request->id])->update([
                    'etims_code' => $responseData['data']['itemCode'],
                ] +
                    $request->only(
                        'item_classification_id',
                        'etims_item_type_id',

                        'etims_origin_country_id',
                        'etims_item_packaging_code_id'
                    ));

                $data =   Stock::with(['product_category', 'unit', 'tax_dept'])->where(['id' => $request->id])->first();

                return response()->json($data);
            } else {
                return response()->json(['error' => $responseData], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateItems(Request $request)
    {
        $request->validate([
            'item_classification_id' => "required",
            'etims_item_type_id' => "required",
            'etims_origin_country_id' => "required",
            'etims_item_packaging_code_id' => "required",
            'id' => 'required',
            'current_qty' => "numeric|required",
            'etims_code' => 'required'
        ]);
        try {
            $url = Parent::pgmBaseUrl() . 'items/' . $request->etims_code;
            $itemClassification =  ItemClassification::find($request->item_classification_id);
            $etimsItemType = EtimsItemType::find($request->etims_item_type_id);
            $etimsOriginCountry = EtimsOriginCountry::find($request->etims_origin_country_id);
            $etimsItemPackagingCode = EtimsItemPackagingCode::find($request->etims_item_packaging_code_id);
            $taxDept = TaxDept::find($request->tax_dept_id);

            if (!$taxDept) {
                return response()->json(['error' => "Tax code missing"], 500);
            }
            $unit = Unit::find($request->unit_id);
            if (!$unit) {
                return response()->json(['error' => "Tax code missing"], 500);
            }
            $stock = Stock::find($request->id);

            $data = [
                "name" => $stock->product_name,
                "orgCountryCode" => $etimsOriginCountry->code,
                "unitPrice" => $stock->selling_price,
                "itemTypeCode" => $etimsItemType->code,
                "taxCode" => $taxDept->tax_code,
                "qtyUnitCode" =>  $unit->code,
                "pkgUnitCode" =>  $etimsItemPackagingCode->code,
                "itemClassCode" => $itemClassification->code,
                "stock" => (float)$request->current_qty,
            ];


            $response = Http::withHeaders(Parent::pgmApiHeaders())->put($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();
            
            if ($statusCode == 200) {
                Stock::where(['id' => $request->id])->update(
                    $request->only(
                        'item_classification_id',
                        'etims_item_type_id',
                        'tax_dept_id',
                        'unit_id',
                        'etims_origin_country_id',
                        'etims_item_packaging_code_id'
                    )
                );

                $data =   Stock::with(['product_category', 'unit', 'tax_dept'])->where(['id' => $request->id])->first();

                return response()->json($data);
            } else {
                return response()->json(['error' => $responseData], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stockMovt(Request $request)
    {
        $request->validate([

            'id' => 'required',
            'qty' => "numeric|required|gt:0",
            'etims_code' => 'required'
        ]);
        try {
            $url = Parent::pgmBaseUrl() . 'stock/transfer/queries/receive';



            $stock = Stock::with(['tax_dept'])->find($request->id);
            $stockItems[] = array(
                'itemCode' => $stock->etims_code,
                'qty' => (float)$request->qty,
                'unitPrice' => (float)$stock->purchase_price,
                'discountAmount' => 0,
                'taxAmount' => ceil($this->calculateTax($stock->tax_dept->tax_rate, ($stock->purchase_price * $request->qty))),
                'taxableAmount' => ceil($stock->purchase_price * $request->qty),
                'amount' => ceil($stock->purchase_price * $request->qty)
            );
            $data = [
                "totalAmount" => ceil($stock->purchase_price * $request->qty),
                "totalTaxableAmount" => ceil($stock->purchase_price * $request->qty),
                "totalTaxAmount" => ceil($this->calculateTax($stock->tax_dept->tax_rate, ($stock->purchase_price * $request->qty))),
                "stockItems" => $stockItems,

            ];


            $response = Http::withHeaders(Parent::pgmApiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();

            if ($statusCode == 200) {
                return response()->json($responseData);
            } else {
                return response()->json(['error' => $responseData], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function syncwithEtims(Request $request)
    {
        ini_set('max_execution_time', 4800);
        try {
            $url = Parent::pgmBaseUrl() . 'stock/transfer/queries/receive';
            $inventory = Inventory::with(['stock.tax_dept'])->where('qty', '>', 0)->where(['department_id' => $request->department_id])->get();


            $stockItems = [];
            $totalAmount = 0;
            $totalTaxableAmount = 0;
            $totalTaxAmount = 0;
            foreach ($inventory as  $value) {
                if ($value['stock']['etims_code']) {
                    $totalAmount += ($value['stock']['purchase_price'] * $value['qty']);
                    $totalTaxableAmount += ($this->calculateTax($value['stock']['tax_dept']['tax_rate'], ($value['stock']['purchase_price'] * $value['qty'])));
                    $totalTaxAmount = $value['stock']['purchase_price'] * $value['qty'];
                    $stockItems[] = [
                        "itemCode" => $value['stock']['etims_code'],
                        'qty' => (float) $value['qty'],
                        'unitPrice' => (float) $value['stock']['purchase_price'],
                        'discountAmount' => 0,
                        'taxAmount' => ceil($this->calculateTax($value['stock']['tax_dept']['tax_rate'], ($value['stock']['purchase_price'] * $value['qty']))),
                        'taxableAmount' => ceil($value['stock']['purchase_price'] * $value['qty']),
                        'amount' => ceil($value['stock']['purchase_price'] * $value['qty'])

                    ];
                }
            }
            $data = [
                "totalAmount" => ceil($totalAmount),
                "totalTaxableAmount" => ceil($totalTaxableAmount),
                "totalTaxAmount" => ceil($totalTaxAmount),
                "stockItems" => $stockItems,

            ];
           // print_r(json_encode($data));
            if (count($stockItems) > 0) {
                $response = Http::withHeaders(Parent::pgmApiHeaders())->post($url, $data);


                $responseData = $response->json();
                $statusCode = $response->status();

                if ($statusCode == 200) {
                    return response()->json($responseData);
                } else {
                    return response()->json(['error' => $responseData], 500);
                }
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
