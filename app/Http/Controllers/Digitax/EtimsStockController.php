<?php

namespace App\Http\Controllers\Digitax;

use Exception;
use App\Unit\Unit;
use App\Stock\Stock;
use App\tax\TaxDept;
use App\dept\Department;
use App\Inventory\Inventory;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use App\EtimStock\EtimsItemType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\EtimStock\EtimsOriginCountry;
use App\EtimStock\ItemClassification;
use App\Http\Controllers\BaseController;
use App\EtimStock\EtimsItemPackagingCode;
use App\Http\Requests\Digitax\DigitaxStockRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Scopes\BranchScope;

class EtimsStockController extends BaseController
{

    public function bulkAddItems(Request $request)
    {
        // Get the last occurrence of each required reference from the request
        // Fetch the latest (most recent) referenced records, not from user input
        $itemClassification = ItemClassification::latest()->first();
        $etimsItemType = EtimsItemType::latest()->first();
        $etimsOriginCountry = EtimsOriginCountry::latest()->first();
        $etimsItemPackagingCode = EtimsItemPackagingCode::latest()->first();

        $errors = [];

        if (!$itemClassification) {
            $errors['item_classification_id'] = 'Invalid item classification ID';
        }
        if (!$etimsItemType) {
            $errors['etims_item_type_id'] = 'Invalid ETIMS item type ID';
        }
        if (!$etimsOriginCountry) {
            $errors['etims_origin_country_id'] = 'Invalid origin country ID';
        }
        if (!$etimsItemPackagingCode) {
            $errors['etims_item_packaging_code_id'] = 'Invalid item packaging code ID';
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }
        $lastProcessedId = $request->last_id;
        // Fetch all stocks where item_id is null
        $stocks = Stock::whereNull('item_id')->where('id', '>', $lastProcessedId)->limit(30)->get();

        if ($stocks->isEmpty()) {
            return response()->json(['message' => 'No stocks to sync.'], 200);
        }

        $results = [];
        foreach ($stocks as $stock) {
            // Fetch TaxDept and Unit from Stock
            $taxDept = TaxDept::find($stock->tax_dept_id);
            $unit = Unit::find($stock->unit_id);

            if (!$taxDept || !$unit) {
                $results[] = [
                    'stock_id' => $stock->id,
                    'status' => 'failed',
                    'reason' => 'Missing TaxDept or Unit'
                ];
                continue;
            }

            $data = [
                "item_class_code" => $itemClassification->code,
                "item_type_code" => $etimsItemType->code,
                "item_name" => $stock->product_name,
                "origin_nation_code" => $etimsOriginCountry->code,
                "package_unit_code" => $etimsItemPackagingCode->code,
                "quantity_unit_code" => $unit->code,
                "tax_type_code" => $taxDept->tax_code,
                "default_unit_price" => ($stock->selling_price),
              
                "is_stock_item" => $stock->inventory_type == 'inventory' ? true : false,
            ];

            try {
                $url = Parent::digitaxBaseUrl() . 'items';
                $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);
                $responseData = $response->json();
                $statusCode = $response->status();

                if ($statusCode == 201 && isset($responseData['id'])) {
                    // Update stock with new item_id and reference fields, also update tax_id from API result if present
                    $updateData = [
                        'item_id' => $responseData['id'],
                        'item_classification_id' => $itemClassification->id,
                        'etims_item_type_id' => $etimsItemType->id,
                        'etims_origin_country_id' => $etimsOriginCountry->id,
                        'etims_item_packaging_code_id' => $etimsItemPackagingCode->id,
                    ];
                    if (isset($responseData['tax_id'])) {
                        $updateData['tax_id'] = $responseData['tax_id'];
                    }
                    $stock->update($updateData);

                    $results[] = [
                        'stock_id' => $stock->id,
                        'status' => 'success',
                        'item_id' => $responseData['id']
                    ];
                    $lastIdProcessed = $stock->id;
                } else {
                    $results[] = [
                        'stock_id' => $stock->id,
                        'status' => 'failed',
                        'reason' => $responseData
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'stock_id' => $stock->id,
                    'status' => 'failed',
                    'reason' => $e->getMessage()
                ];
            }
        }
        return response()->json([
            'message' => 'Batch processed',
            'last_id' => $lastIdProcessed
        ], 200);
    }
    public function addItems(Request $request)
    {
        $request->validate([
            'item_classification_id' => "required",
            'etims_item_type_id' => "required",
            'etims_origin_country_id' => "required",
            'etims_item_packaging_code_id' => "required",
            'id' => 'required',
        ]);
        try {
            $url = Parent::digitaxBaseUrl() . 'items';
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
                "item_class_code" => $itemClassification->code,
                "item_type_code" => $etimsItemType->code,
                "item_name" => $stock->product_name,
                "origin_nation_code" => $etimsOriginCountry->code,
                "package_unit_code" =>  $etimsItemPackagingCode->code,
                "quantity_unit_code" =>  $unit->code,
                "tax_type_code" => $taxDept->tax_code,
                "default_unit_price" => $stock->selling_price,
                
                "is_stock_item" =>  $stock->inventory_type == 'inventory' ? true : false,
            ];


            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();
            // print_r($responseData);
            //    print_r($statusCode);
            if ($statusCode == 201) {
                Stock::where(['id' => $request->id])->update([
                    'item_id' => $responseData['id'],



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
            'product_name' => 'required',
            'item_id' => 'required'
        ]);
        try {
            $url = Parent::digitaxBaseUrl() . 'items/' . $request->item_id;
            $itemClassification =  ItemClassification::find($request->item_classification_id);
            $etimsItemType = EtimsItemType::find($request->etims_item_type_id);
            $etimsOriginCountry = EtimsOriginCountry::find($request->etims_origin_country_id);
            $etimsItemPackagingCode = EtimsItemPackagingCode::find($request->etims_item_packaging_code_id);
            $taxDept = TaxDept::find($request->tax_dept_id);
            $unit = Unit::find($request->unit_id);
            $stock = Stock::find($request->id);
            $data = [
                "item_class_code" => $itemClassification->code,
                "item_type_code" => $etimsItemType->code,
                "item_name" => $request->product_name,
                "origin_nation_code" => $etimsOriginCountry->code,
                "package_unit_code" =>  $etimsItemPackagingCode->code,
                "quantity_unit_code" =>  $unit->code,
                "tax_type_code" => $taxDept->tax_code,
                "default_unit_price" => (float)  $request->selling_price,
                "is_stock_item" => $stock->inventory_type == 'inventory' ? true : false,
            ];



            $response = Http::withHeaders(Parent::apiHeaders())->put($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();






            if ($statusCode == 200) {
                Stock::where(['id' => $request->id])->update([
                    'name' => $request->product_name,



                ] +
                    $request->only(
                        'item_classification_id',
                        'etims_item_type_id',
                        'product_name',
                        'selling_price',
                        'tax_dept_id',
                        'etims_origin_country_id',
                        'etims_item_packaging_code_id'
                    ));

                $data =   Stock::with(['product_category', 'unit', 'tax_dept'])->where(['id' => $request->id])->first();

                return response()->json($data);
            } else {
                return response()->json(['error' => $responseData], 500);
            }


            $data =   Stock::with(['product_category', 'unit', 'tax_dept'])->where(['id' => $request->id])->first();

            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function updateStockQtyEtims1(UpdateInventoryRequest $request)
    {
        try {
            $url = Parent::digitaxBaseUrl() . 'stock/master';

            $data = [
                "item_id" => $request->item_id,
                "remaining_quantity" => (float) $request->curr_qty,

            ];



            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();


            $statusCode = $response->status();



            // DB::transaction(function () use ($request) {
            //     Inventory::updateOrCreate(
            //         ['stock_id' => $request->stock_id, 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
            //         ['qty' =>  $request->curr_qty]
            //     );
            //     PhysicalStock::create([
            //         'department_id' => $request->department_id,
            //         'qty' => $request->curr_qty,
            //         'stock_id' => $request->stock_id,
            //         'type' => $request->type,

            //     ] + Parent::user_branch_id());
            // }, 5);


            if ($statusCode == 201) {
                return true;
            } else {
                return response()->json(['status' => "error", "body" => "Etims Server  error"], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateStockQtyEtims(UpdateInventoryRequest $request)
    {
        try {
            $url = Parent::digitaxBaseUrl() . 'stock/adjust';

            $data = [
                "item_id" => $request->item_id,
                "quantity" => (float)$request->curr_qty,
                "action" => "add",
                "type" => "adjustment"
            ];



            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();


            $statusCode = $response->status();



            // DB::transaction(function () use ($request) {
            //     Inventory::updateOrCreate(
            //         ['stock_id' => $request->stock_id, 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
            //         ['qty' =>  $request->curr_qty]
            //     );
            //     PhysicalStock::create([
            //         'department_id' => $request->department_id,
            //         'qty' => $request->curr_qty,
            //         'stock_id' => $request->stock_id,
            //         'type' => $request->type,

            //     ] + Parent::user_branch_id());
            // }, 5);


            if ($statusCode == 201) {
                return true;
            } else {
                return response()->json(['status' => "error", "body" => "Etims Server  error"], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    function bulkInventoyEtimsSync()
    {
        ini_set('max_execution_time', 0);

        // $inventory = Inventory::with(['stock'])->where('qty', '>', 0)->where('department_id', $dept->id)->get();
        $stock = Stock::get();
        if (!$stock->isEmpty()) {
            foreach ($stock as  $value) {




                $item_id = $value['item_id'];


                if ($item_id) {

                    try {


                        $url = Parent::digitaxBaseUrl() . 'stock/adjust';
                        $amount = 2000;
                        $data = [
                            "item_id" => $item_id,
                            "quantity" => (float)$amount, //(float)$value['qty'],
                            "action" => "add",
                            "type" => "adjustment"
                        ];



                        $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


                        $responseData = $response->json();


                        $statusCode = $response->status();
                    } catch (Exception $e) {
                        echo ">>" . $e->getMessage();
                        // return response()->json(['error' => $e->getMessage()], 500);
                    }
                }
            }
        }
    }


    //bulk sync with zero qty

    function bulkInventoyEtimsSyncZeroQty(Request $request)
    {
        ini_set('max_execution_time', 0);

     

        // Track progress using session, cache, or database
        $lastProcessedId = $request->input('last_id', 0);  // Get the last processed ID
       
 
     $inventory = Stock::whereNotNull('item_id')->where('id', '>', $lastProcessedId)->limit(50)->get();

        $lastIdProcessed = null;

        foreach ($inventory as $value) {
            $item_id = $value['item_id'];

            if ($item_id) {
                try {
                    

                    
                       
                            $url = Parent::digitaxBaseUrl() . 'stock/adjust';
                            $data = [
                                "item_id" => $item_id,
                                "quantity" => 100000.00, //(float)$value['qty'],
                                "action" => "add",
                                "type" => "adjustment"
                            ];
                            Http::withHeaders(Parent::apiHeaders())->post($url, $data);
                       
                    

                    $lastIdProcessed = $value->id; // Track last processed ID

                } catch (Exception $e) {
                    // Log error but continue
                    Log::error("Sync Error: " . $e->getMessage());
                }
            }
        }

        // Return the last processed ID to continue in next request
        if ($inventory->isEmpty()) {
            return response()->json([
                'message' => 'Sync completed',
                'last_id' => null
            ], 200);
        }

        return response()->json([
            'message' => 'Batch processed',
            'last_id' => $lastIdProcessed
        ], 200);
    }
}
