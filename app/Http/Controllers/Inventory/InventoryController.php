<?php

namespace App\Http\Controllers\Inventory;

use App\Stock\Stock;
use App\POS\POSCredit;
use App\Stock\AddStock;
use App\Invoices\Invoice;
use App\Stock\IssueStock;
use App\Stock\MergeStock;
use App\Stock\StockWaste;
use App\Logs\InventoryLog;
use App\Returns\ReturnGRN;
use App\Stock\DeductStock;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use App\Stock\PhysicalStock;
use Illuminate\Http\Request;
use App\CreditNote\CreditNote;
use App\Inventory\BulkStockTake;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\StockTransfer\StockTransfer;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\Builder;
use App\StockTransfer\ReceiveBranchTransfer;
use App\PurchaseOrder\PurchaseOrderReceivable;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use Carbon\Carbon;

class InventoryController extends BaseController
{

    public function fetch()
    {
        DB::enableQueryLog();

        $main_query = Inventory::excludeSoftDeletedStock()
            ->with([
                'department',
                'branch',
                'stock.branchshelves.shelf',
                'stock.product_category',
                'stock.unit',
                'stock.tax_dept',
                'stock.department'
            ])
            ->select([
                'inventories.*',
                DB::raw('stocks.purchase_price * inventories.qty as total_purchase_value'),
                DB::raw('stocks.selling_price * inventories.qty as total_selling_value'),
                DB::raw('COALESCE(shelves.name, "") as shelf_name')
            ])
            ->join('stocks', 'inventories.stock_id', '=', 'stocks.id')
            ->leftJoin('branchshelves', function ($join) {
                $join->on('stocks.id', '=', 'branchshelves.stock_id')
                    ->on('branchshelves.branch_id', '=', 'inventories.branch_id')
                    ->whereNull('branchshelves.deleted_at');;
            })
            ->leftJoin('shelves', 'branchshelves.shelf_id', '=', 'shelves.id')
            ->where('inventories.qty', '!=', 0)
            ->when(request('sort_shelf'), function ($query) {
                $query->orderBy('shelf_name', request('sort_shelf'));
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                            ->orWhereHas('unit', function ($query) {
                                $query->where('name', 'LIKE', '%' . request('search') . '%');
                            });
                    })
                        ->orWhereHas('department', function ($query) {
                            $query->where('department', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('branch', function ($query) {
                            $query->where('branch', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->when(request('department_id', '') != '', function ($query) {
                $query->where('inventories.department_id', request('department_id'));
            })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            })
            ->when(request('sort_purchase_value'), function ($query) {
                $query->orderBy('total_purchase_value', request('sort_purchase_value'));
            })
            ->when(request('sort_selling_value'), function ($query) {
                $query->orderBy('total_selling_value', request('sort_selling_value'));
            })
            ->when(request('sort_purchase_price'), function ($query) {
                $query->orderBy('stocks.purchase_price', request('sort_purchase_price'));
            })
            ->when(request('sort_selling_price'), function ($query) {
                $query->orderBy('stocks.selling_price', request('sort_selling_price'));
            });

        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }

        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data = $main_query->get();
        }

        return response()->json($data);
    }

    public function fetch2()
    {
        DB::enableQueryLog();
        $main_query = Inventory::excludeSoftDeletedStock()
            ->with([
                'department',
                'branch',
                'stock.branchshelves.shelf',
                'stock.product_category',
                'stock.unit',
                'stock.tax_dept',
                'stock.department'
            ])
            ->select([
                'inventories.*',
                DB::raw('stocks.purchase_price * inventories.qty as total_purchase_value'),
                DB::raw('stocks.selling_price * inventories.qty as total_selling_value')
            ])

            ->join('stocks', 'inventories.stock_id', '=', 'stocks.id')


            ->where('inventories.qty', '!=', 0)

            ->when(request('sort_shelf'), function ($query) {
                $query->orderBy('stock_shelf.shelf_name', request('sort_shelf'));
            })
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('unit', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                })
                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('branch', function ($query) {
                        $query->where('branch', 'LIKE', '%' . request('search') . '%');
                    });
            })
            ->when(request('department_id', '') != '', function ($query) {
                $query->where('inventories.department_id', request('department_id'));
            })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            })
            ->when(request('sort_purchase_value'), function ($query) {
                $query->orderBy('total_purchase_value', request('sort_purchase_value'));
            })
            ->when(request('sort_selling_value'), function ($query) {
                $query->orderBy('total_selling_value', request('sort_selling_value'));
            })
            ->when(request('sort_purchase_price'), function ($query) {

                $query->orderBy('stocks.purchase_price', request('sort_purchase_price'));
            })
            ->when(request('sort_selling_price'), function ($query) {

                $query->orderBy('stocks.selling_price', request('sort_selling_price'));
            });

        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }

        // print_r(DB::getQueryLog());
        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data = $main_query->get();
        }
        return response()->json($data);
    }


    function fetchInventoryValue()
    {


        $main_query = Inventory::excludeSoftDeletedStock()->with([

            'department',
            'branch',
            'stock',
            'stock.product_category',
            'stock.unit',
            'stock.tax_dept',
            'stock.department'
        ])->when(request('search', '') != '', function ($query) {
            $query->whereHas('department', function ($query) {
                //   $query->where('inventories.department', 'LIKE', '%' . request('search') . '%');
            });
        });
        $main_query->join('stocks', 'inventories.stock_id', '=', 'stocks.id');

        $main_query->when(request('department_id', '') != '', function ($query) {
            $query->where('inventories.department_id', request('department_id'));
        });

        $main_query->selectRaw('SUM(inventories.qty) AS sum_qty,SUM(stocks.selling_price*inventories.qty) AS value_on_sp,SUM(stocks.purchase_price*inventories.qty) AS value_on_pp');
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        $data = $main_query->first();

        return response()->json($data);
    }
    function searchItem()
    {


        $main_query = Inventory::with([

            'department',
            'branch',
            'stock',
            'stock.product_category',
            'stock.unit',
            'stock.tax_dept',
            'stock.department'
        ])
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                });
            });


        $main_query->orderBy('id', 'DESC')->where('branch_id', Parent::branch_id()); //->groupBy('department_id');
        $data = $main_query->get();

        if (request('page') > 0) {

            $data = $main_query->paginate(50);
        }
        return response()->json($data);
    }
    function searchItemChunk()
    {


        $main_query = Inventory::with([

            'department',
            'branch',
            'stock',
            'stock.product_category',
            'stock.unit',
            'stock.tax_dept',
            'stock.department'
        ])
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                });
            });


        $main_query->orderBy('id', 'DESC'); //->groupBy('department_id');
        $data = $main_query->skip(0)->take(50)->get();


        return response()->json($data);
    }


    function updateStockQty(UpdateInventoryRequest $request)
    {
        DB::transaction(function () use ($request) {
            $inventory = Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->first();
            $original_qty = $inventory ? $inventory->qty : 0;
            Inventory::updateOrCreate(
                ['stock_id' => $request->stock_id, 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                ['qty' =>  $request->curr_qty]
            );

            $stock = Stock::where('id', $request->stock_id)->first();
            PhysicalStock::create([
                'department_id' => $request->department_id,
                'qty' => $request->curr_qty,
                'stock_id' => $request->stock_id,
                'type' => $request->type,
                'original_qty' => $original_qty,
                'selling_price' => $stock->selling_price,
                'purchase_price' => $stock->purchase_price,


            ] + Parent::user_branch_id());
        }, 5);
        return true;
    }

    function addDeductStockQty(UpdateInventoryRequest $request)
    {
        DB::transaction(function () use ($request) {
            $inventory = Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->first();
            $original_qty = $inventory ? $inventory->qty : 0;
            if ($request->type == 'decrement') {
                Inventory::updateOrCreate(
                    ['stock_id' => $request->stock_id, 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty -' . $request->curr_qty)]
                );
                DeductStock::create([
                    'department_id' => $request->department_id,
                    'qty' => $request->curr_qty,
                    'stock_id' => $request->stock_id,
                    'unique_code' => Parent::generateDocNumber("DEDUCT_")
                ] + Parent::user_branch_id());
            }
            if ($request->type == 'increment') {
                Inventory::updateOrCreate(
                    ['stock_id' => $request->stock_id, 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $request->curr_qty)]
                );
                AddStock::create([
                    'department_id' => $request->department_id,
                    'qty' => $request->curr_qty,
                    'stock_id' => $request->stock_id,
                    'entry_code' => Parent::generateDocNumber("ADD_")
                ] + Parent::user_branch_id());
            }

            $stock = Stock::where('id', $request->stock_id)->first();
            PhysicalStock::create([
                'department_id' => $request->department_id,
                'qty' => $request->curr_qty,
                'stock_id' => $request->stock_id,
                'type' => $request->type,
                'original_qty' => $original_qty,
                'selling_price' => $stock->selling_price,
                'purchase_price' => $stock->purchase_price,


            ] + Parent::user_branch_id());
        }, 5);
        return true;
    }
    public function emptyInventory(Request $request)
    {
        if ($request->department_id) {
            Inventory::where('department_id', $request->department_id)->delete();
        } else {
            Inventory::query()->delete();
        }
        return true;
    }
    public function destroy(Request $request)
    {
        Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->delete();
        return true;
    }
    function getItemQty()
    {
        $id = request('id');
        $res = Inventory::with(['department', 'stock'])->where(["stock_id" => $id])->get();
        return response()->json($res);
    }
    function queryHistory($stockId)
    {
        $purchaseOrders = PurchaseOrderReceivable::select(
            'purchase_order_receivables.id',
            'purchase_order_receivables.delivery_no as ref',
            'purchase_order_receivables.id',
            'purchase_order_receivables.qty_delivered as qty',
            'purchase_order_receivables.stock_id',
            'purchase_order_receivables.created_at'
        )
            ->where('stock_id', $stockId)

            ->get();

        // Fetch Physical Stocks
        $physicalStocks = IssueStock::select(
            'id',
            'issue_no as ref',
            'id',
            'qty_issued as qty',
            'stock_id',
            'created_at'
        )
            ->where('stock_id', $stockId)

            ->get();
        $saleDetailsReports = AllSaleDetailsReport::select(
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'order_no as ref'
        )
            ->where('stock_id', $stockId)->where('qty', '!=', 0)

            ->get();
        $creditReport = POSCredit::select(
            'id',
            DB::raw('qty as qty'),
            'stock_id',
            'created_at',
            'credit_no as ref'
        )
            ->where('stock_id', $stockId)

            ->get();

        $stockWaste = StockWaste::select(
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'waste_no as ref'
        )
            ->where('stock_id', $stockId)->where('qty', '!=', 0)

            ->get();
        $invoiceReport = Invoice::select(
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'invoice_no as ref'
        )
            ->where('stock_id', $stockId)->where('qty', '!=', 0)

            ->get();
        $purchaseReturns = ReturnGRN::select(
            'return_g_r_n_s.id',
            'return_g_r_n_s.delivery_no as ref',
            'return_g_r_n_s.id',

            DB::raw('-return_g_r_n_s.qty as qty'),
            'return_g_r_n_s.stock_id',
            'return_g_r_n_s.created_at'
        )
            ->where('stock_id', $stockId)

            ->get();

        $creditNotes = CreditNote::select('id', 'qty', 'created_at', 'credit_no as ref')
            ->where('qty', '!=', 0)
            ->whereHas('invoice', function ($query) use ($stockId) {
                $query->where('stock_id', $stockId);
            })
            ->with(['invoice' => function ($query) use ($stockId) {
                $query->where('stock_id', $stockId);
            }])
            ->get();
        $update = PhysicalStock::select(
            'id',
            'qty',

            'created_at',
            'type as ref'
        )
            ->where(['stock_id' => $stockId, 'type' => 'Manual Update'])
            ->get();
        $deductStock = DeductStock::select(
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'unique_code as ref'
        )
            ->where('stock_id', $stockId)->where('qty', '!=', 0)

            ->get();
        $addStock = AddStock::select(
            'id',
            DB::raw('qty as qty'),
            'stock_id',
            'created_at',
            'entry_code as ref'
        )
            ->where('stock_id', $stockId)->where('qty', '!=', 0)

            ->get();

        $bulkStockTake = BulkStockTake::select(
            'id',
            'counted_qty as qty',

            'created_at',
            'stocktake_code as ref'
        )
            ->where('stock_id', $stockId)
            ->get();
        $branchTransfer = StockTransfer::select(
            'id',

            DB::raw('-qty as qty'),
            'created_at',
            'transfer_code as ref'
        )
            ->where('stock_id', $stockId)
            ->get();
        $branchTransferReceivable = ReceiveBranchTransfer::select(
            'id',
            'qty as qty',

            'created_at',
            'transfer_code as ref'
        )
            ->where('stock_id', $stockId)
            ->get();
        $merged = MergeStock::select(
            'id',
            'qty_merged as qty',

            'created_at',
            'updated_at as ref'
        )
            ->where('stock_id', $stockId)
            ->get();

        // Merge and order the results
        // Merge and order the results

        $result = $purchaseOrders->merge($physicalStocks)->merge($saleDetailsReports)->merge($creditReport)
            ->merge($invoiceReport)->merge($stockWaste)->merge($creditNotes)->merge($purchaseReturns)->merge($update)->merge($addStock)->merge($deductStock)
            ->merge($bulkStockTake)->merge($branchTransfer)
            ->merge($branchTransferReceivable)->merge($merged)->sortBy('created_at'); //->sortByDesc('created_at');
        return $result;
    }
    function fetchMovementHistory()
    {
        $stockId = request('stock_id');
        //request('page');
        // Fetch Purchase Order Receivables
        $result = $this->queryHistory($stockId);
        // print_r(json_encode($saleDetailsReports));
        // Paginate the result manually
        $formattedResult = collect($result)->map(function ($item) {
            $table = '';

            if ($item instanceof PurchaseOrderReceivable) {
                $table = 'Purchase delivered';
            } elseif ($item instanceof IssueStock) {
                $table = 'Dispatch';
            } elseif ($item instanceof AllSaleDetailsReport) {
                $table = 'Retail sale';
            } elseif ($item instanceof StockWaste) {
                $table = 'Stock waste';
            } elseif ($item instanceof POSCredit) {
                $table = 'pos credit note';
            } elseif ($item instanceof Invoice) {
                $table = 'Invoice sales';
            } elseif ($item instanceof CreditNote) {
                $table = 'Invoice Credit Note';
            } elseif ($item instanceof BulkStockTake) {
                $table = 'Stocktake';
            } elseif ($item instanceof PhysicalStock) {
                $table = 'Stock Direct Update';
            } elseif ($item instanceof DeductStock) {
                $table = 'Manual Stock Deduction';
            } elseif ($item instanceof AddStock) {
                $table = 'Manual Stock Increment';
            } elseif ($item instanceof StockTransfer) {
                $table = 'Interbranch Transfer';
            } elseif ($item instanceof ReceiveBranchTransfer) {
                $table = 'Interbranch Receivable';
            } elseif ($item instanceof MergeStock) {
                $table = 'Merged Inventory';
            } elseif ($item instanceof ReturnGRN) {
                $table = 'GRN Returns';
            }





            return [
                'id' => $item->id,
                'ref' => $item->ref,
                'table' => $table,
                'qty' => $item->qty,

                'stock_id' => $item->stock_id,
                'created_at' => $item->created_at,
            ];
        })->values()->all();
        //  $data = $formattedResult;

        $cumulativeQty = 0;

        foreach ($formattedResult as &$item) {
            if ($item['table'] == 'Stock Direct Update') {
                $cumulativeQty = $item['qty'];
            } else if ($item['table'] == 'Stocktake') {
                $cumulativeQty = $item['qty'];
            } else {
                $cumulativeQty += $item['qty'];
            }

            $item['cumulative_qty'] = $cumulativeQty;
        }
        // Create a LengthAwarePaginator for the transformed result
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 100;
        $currentPageItems = collect($formattedResult)->slice(($currentPage - 1) * $perPage, $perPage)->all();

        // Create a LengthAwarePaginator for the transformed result
        $paginatedResult = new LengthAwarePaginator($currentPageItems, count($formattedResult), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);
        return $paginatedResult;
    }
    function fetchItemHistoryExcel()
    {
        $stockId = request('stock_id');
        //request('page');
        // Fetch Purchase Order Receivables
        $result = $this->queryHistory($stockId);
        // print_r(json_encode($saleDetailsReports));
        // Paginate the result manually

        $perPage = 100;


        // Transform the paginated result into the desired array structure
        $formattedResult = collect($result)->map(function ($item) {
            $table = '';
            if ($item instanceof PurchaseOrderReceivable) {
                $table = 'Purchase delivered';
            } elseif ($item instanceof IssueStock) {
                $table = 'Dispatch';
            } elseif ($item instanceof AllSaleDetailsReport) {
                $table = 'Retail sale';
            } elseif ($item instanceof POSCredit) {
                $table = 'pos credit note';
            } elseif ($item instanceof Invoice) {
                $table = 'Invoice sales';
            } elseif ($item instanceof CreditNote) {
                $table = 'Invoice Credit Note';
            }

            return [
                'id' => $item->id,
                'ref' => $item->ref,
                'table' => $table,
                'qty' => $item->qty,
                'stock_id' => $item->stock_id,
                'created_at' => $item->created_at,
            ];
        })->values()->all();



        return $formattedResult;
    }

    public function getLowStock()
    {
        $inventoryItems = Inventory::with(['stock.unit', 'stock.supplier', 'stock.tax_dept'])->where(['department_id' => request('department_id')])->where('qty', '<=', DB::raw('(select reorder_qty from stocks where stocks.id = inventories.stock_id)'))->get();

        return $inventoryItems;
    }
    function fetchBranchComparisonReport()
    {

        $query = Inventory::withoutGlobalScope(BranchScope::class)->select('stocks.code as code', 'stocks.name as stock_name', 'branches.branch as branch_name', 'inventories.qty', DB::raw('SUM(inventories.qty) as total_qty'))
            ->groupBy('stock_id', 'branch_id')->join('stocks', 'inventories.stock_id', '=', 'stocks.id')
            ->join('branches', 'inventories.branch_id', '=', 'branches.id');

        if (request('search')) {

            $query->where('stocks.name', 'LIKE', '%' . request('search') . '%');
        }
        if (request('code')) {
            $query->where('stocks.code', request('code'));
        }
        $inventoryData = $query->get();
        // Initialize an empty array to store the stock quantities
        $stockQuantities = [];

        // Iterate through the inventory data and accumulate the quantities
        foreach ($inventoryData as $data) {
            $stockName = $data->stock_name;
            $branchName = $data->branch_name;
            $qty = $data->total_qty;
            $code = $data->code;
            // Check if the stock name exists in the array
            if (!isset($stockQuantities[$stockName])) {
                $stockQuantities[$stockName] = [
                    'stock_name' => $stockName,
                    'code' => $code,
                    'data' => [] // Initialize an empty array to store branch quantities
                ];
            }

            // Add the branch quantity to the data array under the stock
            $stockQuantities[$stockName]['data'][] = [
                'branch' => $branchName,
                'branch_qty' => $qty
            ];
        }

        // Convert the associative array into indexed array
        $stockQuantities = array_values($stockQuantities);


        $perPage = 50;

        // Get the current page from the query string or default to 1
        $page = request('page');

        // Paginate the $stockQuantities array
        $paginatedStockQuantities = new LengthAwarePaginator(
            array_slice($stockQuantities, ($page - 1) * $perPage, $perPage),
            count($stockQuantities),
            $perPage

        );

        // Output the paginated data in JSON format
        return response()->json($paginatedStockQuantities);
    }
    function fetchBranchComparisonReport1()
    {

        DB::enableQueryLog();
        $main_query = Inventory::withoutGlobalScope(BranchScope::class)->with([

            'department' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            },
            'branch',
            'stock.shelf',
            'stock.product_category',
            'stock.unit',
            'stock.tax_dept',
            'stock.department'
        ])
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('unit', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                })
                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('branch', function ($query) {
                        $query->where('branch', 'LIKE', '%' . request('search') . '%');
                    });
            });


        $main_query->when(request('department_id', '') != '', function ($query) {
            $query->where('department_id', request('department_id'));
        });
        $main_query->selectRaw('*,SUM(qty) as sum_qty')->groupBy(['stock_id', 'branch_id']);
        if (request('show_desc') == "true") {

            $main_query->orderBy('qty', 'DESC');
        } else {

            $main_query->orderBy('qty', 'ASC');
        }




        $data = $main_query->get();
        //  print_r(DB::getQueryLog());
        if (request('page') > 0) {

            $data = $main_query->paginate(50);
        }
        return response()->json($data);
    }
    function syncInventoryFromStockList(Request $request)
    {
        ini_set('max_execution_time', 4800);
        $stock_array = Stock::get();
        foreach ($stock_array as  $value) {
            Inventory::firstOrCreate(
                ['stock_id' => $value->id, 'department_id' => $request->department_id],
                ['qty' => 0, 'branch_id' => Parent::branch_id()]
            );
        }
        return response()->json(array("status" => 'added'));
    }

    function inventoryTotals()
    {
        $totalQty           = 'SUM(inventories.qty)';
        $totalSellValue     = 'SUM(inventories.qty * stocks.selling_price)';
        $totalPurchaseValue = 'SUM(inventories.qty * stocks.purchase_price)';

        $sortQtyOrder           = strtoupper(request('sort_total_qty'));
        $sortSellValueOrder     = strtoupper(request('sort_total_value_selling_price'));
        $sortPurchaseValueOrder = strtoupper(request('sort_total_value_purchase_price'));
        $sortProfitOrder        = strtoupper(request('sort_profit_markup'));

        $query = Inventory::withoutGlobalScope(BranchScope::class)
            ->excludeSoftDeletedStock()
            ->with(['stock:id,name', 'branch:id,branch'])
            ->join('stocks',   'stocks.id',   '=', 'inventories.stock_id')
            ->join('branches', 'branches.id', '=', 'inventories.branch_id')

            ->select([
                'inventories.branch_id',
                'branches.branch AS branch_name',
                DB::raw("$totalQty AS total_qty"),
                DB::raw("$totalSellValue AS total_value_selling_price"),
                DB::raw("$totalPurchaseValue AS total_value_purchase_price"),
                DB::raw("
                CASE
                    WHEN $totalSellValue = 0 THEN NULL
                    ELSE
                        ROUND((($totalSellValue - $totalPurchaseValue) * 100) / $totalSellValue, 2)
                END AS profit_markup
            "),
            ])
            ->groupBy('inventories.branch_id', 'branches.branch');

        if (in_array($sortQtyOrder, ['ASC', 'DESC'])) {
            $query->orderByRaw("$totalQty $sortQtyOrder");
        }

        if (in_array($sortSellValueOrder, ['ASC', 'DESC'])) {
            $query->orderByRaw("$totalSellValue $sortSellValueOrder");
        }

        if (in_array($sortPurchaseValueOrder, ['ASC', 'DESC'])) {
            $query->orderByRaw("$totalPurchaseValue $sortPurchaseValueOrder");
        }

        if (in_array($sortProfitOrder, ['ASC', 'DESC'])) {
            $query->orderByRaw("(($totalSellValue - $totalPurchaseValue) * 100 / NULLIF($totalSellValue, 0)) $sortProfitOrder");
        }

        return $query->get();
    }
    function fetchStockHistory($stockId, $startDate = null, $endDate = null, $perPage = 20, $page = 1)
    {
        // Base query builder for all models
        $query = function ($model, $selectFields, $whereConditions = [], $withRelations = []) use ($stockId) {
            $q = $model::select($selectFields)->where('stock_id', $stockId);

            if (!empty($whereConditions)) {
                $q->where($whereConditions);
            }

            if (!empty($withRelations)) {
                $q->with($withRelations);
            }

            return $q;
        };

        // Apply date filters if provided
        $dateFilter = function ($q) use ($startDate, $endDate) {
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
            'purchase_order_receivables.id',
            'purchase_order_receivables.delivery_no as ref',
            'purchase_order_receivables.qty_delivered as qty',
            'purchase_order_receivables.stock_id',
            'purchase_order_receivables.created_at'
        ])->get();

        $physicalStocks = $query(IssueStock::class, [
            'id',
            'issue_no as ref',
            'qty_issued as qty',
            'stock_id',
            'created_at'
        ])->get();

        $saleDetailsReports = $query(AllSaleDetailsReport::class, [
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'order_no as ref'
        ], ['qty', '!=', 0])->get();

        $creditReport = $query(POSCredit::class, [
            'id',
            DB::raw('qty as qty'),
            'stock_id',
            'created_at',
            'credit_no as ref'
        ])->get();
        $stockWaste = $query(StockWaste::class, [
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'waste_no as ref'
        ], ['qty', '!=', 0])->get();
        $invoiceReport = $query(Invoice::class, [
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'invoice_no as ref'
        ], ['qty', '!=', 0])->get();

        $creditNotes = $query(CreditNote::class, [
            'id',
            DB::raw('qty as qty'),
            'created_at',
            'credit_no as ref'
        ], ['qty', '!=', 0], [
            'invoice' => function ($query) use ($stockId) {
                $query->where('stock_id', $stockId);
            }
        ])->get();

        $update = $query(PhysicalStock::class, [
            'id',
            'qty',
            'created_at',
            'type as ref'
        ], ['type' => 'Manual Update'])->get();

        $deductStock = $query(DeductStock::class, [
            'id',
            DB::raw('-qty as qty'),
            'stock_id',
            'created_at',
            'unique_code as ref'
        ], ['qty', '!=', 0])->get();

        $addStock = $query(AddStock::class, [
            'id',
            DB::raw('qty as qty'),
            'stock_id',
            'created_at',
            'entry_code as ref'
        ], ['qty', '!=', 0])->get();

        $bulkStockTake = $query(BulkStockTake::class, [
            'id',
            'counted_qty as qty',
            'created_at',
            'stocktake_code as ref'
        ])->get();

        $branchTransfer = $query(StockTransfer::class, [
            'id',
            'qty as qty',
            'created_at',
            'transfer_code as ref'
        ])->get();

        $branchTransferReceivable = $query(ReceiveBranchTransfer::class, [
            'id',
            'qty as qty',
            'created_at',
            'transfer_code as ref'
        ])->get();

        $merged = $query(MergeStock::class, [
            'id',
            'qty_merged as qty',
            'created_at',
            'updated_at as ref'
        ])->get();

        // Merge all results
        $result = $purchaseOrders->merge($physicalStocks)
            ->merge($saleDetailsReports)
            ->merge($creditReport)
            ->merge($invoiceReport)
            ->merge($creditNotes)
            ->merge($stockWaste)
            ->merge($update)
            ->merge($addStock)
            ->merge($deductStock)
            ->merge($bulkStockTake)
            ->merge($branchTransfer)
            ->merge($branchTransferReceivable)
            ->merge($merged)
            ->sortBy('created_at');

        // Apply date filtering to merged collection
        if ($startDate || $endDate) {
            $result = $result->filter(function ($item) use ($startDate, $endDate) {
                $createdAt = \Carbon\Carbon::parse($item->created_at);
                if ($startDate && $createdAt->lt($startDate)) {
                    return false;
                }
                if ($endDate && $createdAt->gt($endDate)) {
                    return false;
                }
                return true;
            });
        }

        // Convert to array and apply pagination
        $resultArray = $result->values()->toArray();
        $total = count($resultArray);
        $offset = ($page - 1) * $perPage;
        $paginatedData = array_slice($resultArray, $offset, $perPage);

        return [
            'data' => $paginatedData,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

      public function fetchInventoryLogs(Request $request)
    {
        $main_query = InventoryLog::with(['stock', 'department', 'user'])

            // Optional department filter
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $ids = $request->input('department_id');

                // allow "1,2,3" as well as [1,2,3]
                if (is_string($ids) && strpos($ids, ',') !== false) {
                    $ids = collect(explode(',', $ids))
                        ->map(fn($v) => trim($v))
                        ->filter(fn($v) => $v !== '')
                        ->map(fn($v) => (int) $v)
                        ->values()
                        ->all();
                }

                if (is_array($ids)) {
                    // whereIn for multiple, or simple where for single
                    count($ids) > 1
                        ? $q->whereIn('department_id', $ids)
                        : $q->where('department_id', (int) $ids[0]);
                } else {
                    $q->where('department_id', (int) $ids);
                }
            })
            ->when($request->filled('from') && $request->filled('to'), function ($q) use ($request) {
                $fromUtc = Carbon::parse($request->input('from'), 'Africa/Nairobi')->timezone('UTC');
                $toUtc   = Carbon::parse($request->input('to'),   'Africa/Nairobi')->timezone('UTC');
                $q->whereBetween('created_at', [$fromUtc, $toUtc]);
            })
            // Search block
            ->when($request->search, function ($query) use ($request) {
                $s = $request->search;
                $query->where(function ($query) use ($s) {
                    $query->where('action_type', 'LIKE', "%{$s}%")
                        ->orWhereHas('stock', function ($q) use ($s) {
                            $q->where('name', 'LIKE', "%{$s}%")
                                ->orWhere('code', 'LIKE', "%{$s}%")
                                ->orWhere('id', 'LIKE', "%{$s}%");
                        })
                        ->orWhereHas('department', function ($q) use ($s) {
                            $q->where('department', 'LIKE', "%{$s}%");
                        })
                        ->orWhereHas('user', function ($q) use ($s) {
                            $q->where('name', 'LIKE', "%{$s}%");
                        });
                });
            })

            ->latest();
        if (request('page') > 0) {
            $res =     $main_query->paginate(100);
        } else {
            $res = $main_query->get();
        }

        return response()->json($res);
    }}
