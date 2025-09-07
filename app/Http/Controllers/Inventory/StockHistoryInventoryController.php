<?php

namespace App\Http\Controllers\Inventory;

use Carbon\Carbon;
use App\Stock\Stock;
use App\POS\POSCredit;
use App\Stock\AddStock;
use App\Invoices\Invoice;
use App\Stock\IssueStock;
use App\Stock\MergeStock;
use App\Stock\DeductStock;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use App\CreditNote\CreditNote;
use App\Inventory\BulkStockTake;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\StockTransfer\StockTransfer;
use App\Http\Controllers\BaseController;
use App\Inventory\Inventory;
use App\StockTransfer\ReceiveBranchTransfer;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\Stock\StockWaste;
use Illuminate\Pagination\LengthAwarePaginator;

class StockHistoryInventoryController extends BaseController
{
    function fetchPREV($startDate = null, $endDate = null, $perPage = null, $page = null)
    {
        set_time_limit(2400);
        // Get parameters from request if not provided
        $startDate = $startDate ?: request('from');
        $endDate = $endDate ?: request('to');
        $perPage = $perPage ?: request('per_page', 50); // Default to 50 if not provided
        $page = $page ?: request('page', 1);

        // Normalize date inputs to 'Y-m-d' format
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : null;
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : null;

        // Fetch stocks with optional pagination
        $query = Stock::select(['id', 'name', 'selling_price', 'purchase_price']);

        $totalStocks = $query->count();

        // Determine if we should paginate or not
        $shouldPaginate = $page > 0;

        if ($shouldPaginate) {
            $stocks = $query->forPage($page, $perPage)->get();
        } else {
            $stocks = $query->get();
            // When not paginating, set perPage to total count for proper paginator creation
            $perPage = $totalStocks;
        }

        $results = [];

        foreach ($stocks as $stock) {
            $stockId = $stock->id;
            $stockName = $stock->name;
            $sellingPrice = $stock->selling_price;
            $purchasePrice = $stock->purchase_price;

            // Base query builder for all models
            $query = function ($model, $selectFields, $whereConditions = [], $withRelations = []) use ($stockId, $startDate, $endDate) {
                $q = $model::select($selectFields)->where('stock_id', $stockId);

                // Apply where conditions properly
                foreach ($whereConditions as $condition) {
                    if (is_array($condition) && count($condition) === 3) {
                        [$column, $operator, $value] = $condition;
                        $q->where($column, $operator, $value);
                    }
                }

                if (!empty($withRelations)) {
                    $q->with($withRelations);
                }

                // Apply date filters directly in the query
                if ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                }

                return $q;
            };

            // Fetch all data sources
            $purchaseOrders = $query(PurchaseOrderReceivable::class, [
                'purchase_order_receivables.qty_delivered as qty',
                'purchase_order_receivables.created_at'
            ])->get();

            $stockWaste = $query(StockWaste::class, [
                DB::raw('-qty as qty'),
                'created_at'
            ], [['qty', '!=', 0]])->get();

            $physicalStocks = $query(IssueStock::class, [
                'qty_issued as qty',
                'created_at'
            ])->get();

            $saleDetailsReports = $query(AllSaleDetailsReport::class, [
                DB::raw('-qty as qty'),
                'created_at'
            ], [['qty', '!=', 0]])->get();

            $creditReport = $query(POSCredit::class, [
                DB::raw('qty as qty'),
                'created_at'
            ])->get();

            $invoiceReport = $query(Invoice::class, [
                DB::raw('-qty as qty'),
                'created_at'
            ], [['qty', '!=', 0]])->get();

            $update = $query(PhysicalStock::class, [
                'qty',
                'created_at'
            ], [['type', '=', 'Manual Update']])->get();

            $deductStock = $query(DeductStock::class, [
                DB::raw('-qty as qty'),
                'created_at'
            ], [['qty', '!=', 0]])->get();

            $addStock = $query(AddStock::class, [
                DB::raw('qty as qty'),
                'created_at'
            ], [['qty', '!=', 0]])->get();

            $bulkStockTake = $query(BulkStockTake::class, [
                'counted_qty as qty',
                'created_at'
            ])->get();

            $branchTransfer = $query(StockTransfer::class, [
                'qty as qty',
                'created_at'
            ])->get();

            $branchTransferReceivable = $query(ReceiveBranchTransfer::class, [
                'qty as qty',
                'created_at'
            ])->get();

            $merged = $query(MergeStock::class, [
                'qty_merged as qty',
                'created_at'
            ])->get();

            // Merge all results
            $result = $purchaseOrders->merge($physicalStocks)
                ->merge($saleDetailsReports)
                ->merge($creditReport)
                ->merge($invoiceReport)
                ->merge($stockWaste)
                ->merge($update)
                ->merge($addStock)
                ->merge($deductStock)
                ->merge($bulkStockTake)
                ->merge($branchTransfer)
                ->merge($branchTransferReceivable)
                ->merge($merged)
                ->sortBy('created_at');

            // Calculate cumulative quantity
            $cumulativeQty = 0;
            foreach ($result as $item) {
                $cumulativeQty += $item->qty; // Add or deduct based on qty sign
            }

            // Add stock summary to results
            if ($cumulativeQty > 0) {
                $results[] = [
                    'stock_name' => $stockName,
                    'cummulative_qty' => $cumulativeQty,
                    'selling_price' => $sellingPrice,
                    'purchase_price' => $purchasePrice,
                    'total_cost' => $cumulativeQty * $purchasePrice
                ];
            }
        }

        // Prepare pagination URLs
        $paginator = new LengthAwarePaginator(
            $results,
            $totalStocks,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }
    
  private function getSalesAndPurchasesQty($stockId, $fromDate, $toDate = null)
{
    $salesQuery = AllSaleDetailsReport::where('stock_id', $stockId);
    $purchaseQuery = PurchaseOrderReceivable::where('stock_id', $stockId);

    if ($toDate) {
        // Calculate sales and purchases between reset date and target date
        $salesQuery->whereBetween('created_at', [$fromDate, $toDate]);
        $purchaseQuery->whereBetween('created_at', [$fromDate, $toDate]);
    } else {
        // Backward walk: get sales and purchases AFTER the target date
        $salesQuery->where('created_at', '>', $fromDate);
        $purchaseQuery->where('created_at', '>', $fromDate);
    }

    return [
        'sales' => $salesQuery->sum('qty'),
        'purchases' => $purchaseQuery->sum('qty_delivered'),
    ];
}

public function fetch($startDate = null, $perPage = null, $page = null)
{
    set_time_limit(2400);

    $startDate = $startDate ?: request('from');
    $perPage = $perPage ?: request('per_page', 50);
    $page = $page ?: request('page', 1);
    $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->endOfDay()->format('Y-m-d H:i:s') : \Carbon\Carbon::today()->endOfDay()->format('Y-m-d H:i:s');

    $search = request('search');
    $query = Inventory::with('stock');

    if (!empty($search)) {
        $query->whereHas('stock', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    $totalStocks = $query->count();
    $stocks = $query->forPage($page, $perPage)->get();
    $results = [];
    $fullQuery = clone $query;
   $stocksAll = $fullQuery->get();
    foreach ($stocks as $stock) {
        $stockId = $stock->stock_id ?? $stock->id;
        $currentQty = $stock->qty;

        // Find the most recent stock reset before or on startDate
        $physicalStock = PhysicalStock::where('stock_id', $stockId)
            ->where('type', 'Manual Update')
            ->where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->first();

        $bulkStock = BulkStockTake::where('stock_id', $stockId)
            ->where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->first();

        // Determine the latest reset point
        $latestReset = null;
        // if ($physicalStock && $bulkStock) {
        //     $latestReset = $physicalStock->created_at > $bulkStock->created_at ? $physicalStock : $bulkStock;
        // } elseif ($physicalStock) {
        //     $latestReset = $physicalStock;
        // } elseif ($bulkStock) {
        //     $latestReset = $bulkStock;
        // }

        if ($latestReset) {
            // Use reset quantity and adjust from reset date to target date
            $resetQty = $latestReset->counted_qty ?? $latestReset->qty ?? 0;
            $resetDate = \Carbon\Carbon::parse($latestReset->created_at)->format('Y-m-d H:i:s');

            // Get sales and purchases between reset date and startDate
            $qtys = $this->getSalesAndPurchasesQty($stockId, $resetDate, $startDate);
            $adjustedQty = $resetQty + $qtys['sales'] - $qtys['purchases'];
        } else {
            // No reset found, work backward from current quantity
            $qtys = $this->getSalesAndPurchasesQty($stockId, $startDate);
            $adjustedQty = $currentQty + $qtys['sales'] - $qtys['purchases'];
        }

        // Skip non-positive quantities
        if ($adjustedQty <= 0) {
            continue;
        }

        $results[] = [
            'stock_name' => $stock->stock->name ?? $stock->name,
            'cummulative_qty' => $adjustedQty,
            'selling_price' => $stock->stock->selling_price ?? $stock->selling_price,
            'purchase_price' => $stock->stock->purchase_price ?? $stock->purchase_price,
            'total_cost' => $adjustedQty * ($stock->stock->purchase_price ?? $stock->purchase_price),
        ];
    }

    $grandTotalQty = collect($results)->sum('cummulative_qty');
    $grandTotalCost = collect($results)->sum('total_cost');

    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $results,
        $totalStocks,
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return [
        'data' => $paginator,
        'total_cummulative_qty' => $grandTotalQty,
        'total_cost' => $grandTotalCost,
    ];
}
    function fetch1($startDate = null, $endDate = null, $perPage = null, $page = null)
    {
        set_time_limit(2400);

        $startDate = $startDate ?: request('from');
        $endDate = $endDate ?: request('to');
        $perPage = $perPage ?: request('per_page', 50);
        $page = $page ?: request('page', 1);

        $startDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : null;
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : null;

        $query = Stock::select(['id', 'name', 'selling_price', 'purchase_price']);
        $totalStocks = $query->count();

        $stocks = $query->forPage($page, $perPage)->get();
        $results = [];

        foreach ($stocks as $stock) {
            $stockId = $stock->id;

            $queryBuilder = function ($model, $fields, $conditions = []) use ($stockId, $startDate, $endDate) {
                $q = $model::select($fields)->where('stock_id', $stockId);

                foreach ($conditions as [$col, $op, $val]) {
                    $q->where($col, $op, $val);
                }

                if ($startDate) $q->whereDate('created_at', '>=', $startDate);
                if ($endDate) $q->whereDate('created_at', '<=', $endDate);

                return $q;
            };

            $datasets = collect([
                $queryBuilder(PurchaseOrderReceivable::class, ['qty_delivered as qty', 'created_at'])->get(),
                $queryBuilder(StockWaste::class, [DB::raw('-qty as qty'), 'created_at'], [['qty', '!=', 0]])->get(),
                $queryBuilder(IssueStock::class, ['qty_issued as qty', 'created_at'])->get(),
                $queryBuilder(AllSaleDetailsReport::class, [DB::raw('-qty as qty'), 'created_at'], [['qty', '!=', 0]])->get(),
                $queryBuilder(POSCredit::class, ['qty as qty', 'created_at'])->get(),
                $queryBuilder(Invoice::class, [DB::raw('-qty as qty'), 'created_at'], [['qty', '!=', 0]])->get(),
                $queryBuilder(PhysicalStock::class, ['qty', 'created_at'], [['type', '=', 'Manual Update']])->get(),
                $queryBuilder(DeductStock::class, [DB::raw('-qty as qty'), 'created_at'], [['qty', '!=', 0]])->get(),
                $queryBuilder(AddStock::class, ['qty as qty', 'created_at'], [['qty', '!=', 0]])->get(),
                $queryBuilder(BulkStockTake::class, ['counted_qty as qty', 'created_at'])->get(),
                $queryBuilder(StockTransfer::class, ['qty as qty', 'created_at'])->get(),
                $queryBuilder(ReceiveBranchTransfer::class, ['qty as qty', 'created_at'])->get(),
                $queryBuilder(MergeStock::class, ['qty_merged as qty', 'created_at'])->get(),
            ]);

            $allEntries = $datasets->flatten(1)->sortBy('created_at');
            $cumulativeQty = $allEntries->sum('qty');

            if ($cumulativeQty <= 0) continue;

            $results[] = [
                'stock_name' => $stock->name,
                'cummulative_qty' => $cumulativeQty,
                'selling_price' => $stock->selling_price,
                'purchase_price' => $stock->purchase_price,
                'total_cost' => $cumulativeQty * $stock->purchase_price
            ];
        }

        // Calculate grand totals
        $grandTotalQty = collect($results)->sum('cummulative_qty');
        $grandTotalCost = collect($results)->sum('total_cost');

        // Append totals to response
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $results,
            $totalStocks,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'data' => $paginator,
            'total_cummulative_qty' => $grandTotalQty,
            'total_cost' => $grandTotalCost,
        ];
    }


    //
}
