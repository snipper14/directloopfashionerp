<?php

namespace App\Http\Controllers\Inventory;

use App\Stock\IssueStock;
use App\Inventory\Inventory;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use App\Sale\AllSaleDetailsReport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\PurchaseOrder\PurchaseOrderReceivable;

class StockHistoryController extends BaseController
{
    //

    public function getStockMovementHistory()
    {$stockId=request('stock_id');
       

        $salesHistory = AllSaleDetailsReport::where('stock_id', $stockId)->get(['qty', 'created_at as date']);

        $physicalStockHistory = PhysicalStock::where('stock_id', $stockId)->get(['qty', 'created_at as date']);

        $issueStockHistory = IssueStock::where('stock_id', $stockId)->get(['qty_issued', 'created_at as date']);

     //   $purchaseOrderHistory = PurchaseOrderReceivable::where('stock_id', $stockId)->get(['qty_delivered', 'created_at as date']);

        // Merge all histories into a single collection
        $mergedHistory = $$salesHistory
           ->merge($physicalStockHistory)
            ->merge($issueStockHistory);
        //    ->merge($purchaseOrderHistory);

        // Sort the merged history by date in descending order
        $sortedHistory = $mergedHistory->sortByDesc('date')->values();

        // Transform the collection to an array
        $historyArray = $sortedHistory->toArray();

        return response()->json(['stock_movement_history' => $historyArray]);
    }
}
