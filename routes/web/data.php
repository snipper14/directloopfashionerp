<?php

use App\Http\Controllers\BranchAccount\BranchAccountController;
use App\Http\Controllers\BranchStockTransfer\BranchStockTransferController;
use App\Http\Controllers\ChartAccount\ChartAccountController;
use App\Http\Controllers\Credit\POSCreditController;
use App\Http\Controllers\CreditNote\CreditNoteController;
use App\Http\Controllers\Creditors\CreditorsController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\CustomerLedger\CustomerLedgerController;
use App\Http\Controllers\CustomerPayment\CustomerPaymentController;
use App\Http\Controllers\Digitax\EtimsStockController;
use App\Http\Controllers\Expenses\ExpensesController;
use App\Http\Controllers\Expenses\ExpenseTypeController;
use App\Http\Controllers\GeneralLedger\BalanceSheetController;
use App\Http\Controllers\GeneralLedger\GeneralLedgerController;
use App\Http\Controllers\GeneralLedger\GeneralLedgerReportController;
use App\Http\Controllers\GeneralLedger\ProfitLossController;
use App\Http\Controllers\GeneralLedger\TrialBalanceController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\StockHistoryInventoryController;
use App\Http\Controllers\LedgerAccount\LedgerAccountController;
use App\Http\Controllers\Ledgers\MainLedgerAccountController;
use App\Http\Controllers\ModelLogs\ModelLogController;
use App\Http\Controllers\PurchaseOrder\POReceivableController;
use App\Http\Controllers\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\QBReconciliation\QBBankReconciliationController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Returns\GRNReturnController;
use App\Http\Controllers\Sale\POSSaleController;
use App\Http\Controllers\Shelf\ShelfController;
use App\Http\Controllers\Stock\ImportStockExcelController;
use App\Http\Controllers\Stock\ProductMappingController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\Stock\StockUpdateController;
use App\Http\Controllers\SupplierInvoice\SupplierInvoiceController;
use App\Http\Controllers\Waste\WasteRecordController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->namespace('Setting')->group(function () {
    Route::post('/createRole', 'SettingsController@createRole');
    Route::get('/fetchRoles', 'SettingsController@fetchRoles');
    Route::delete('/destroyRole/{id}', 'SettingsController@destroyRole');
    Route::put('/updateRole', 'SettingsController@updateRole');
    Route::post('/assign_roles', 'SettingsController@assign_roles');
    Route::get('/getRoles', 'SettingsController@getRoles');
    Route::post('/createBranch', 'SettingsController@createBranch');
    Route::get('/fetchBranches', 'SettingsController@fetchBranches');
    Route::put('/updateBranch', 'SettingsController@updateBranch');
    Route::delete('/deleteBranch/{id}', 'SettingsController@deleteBranch');
    Route::get('/fetchUsers', 'SettingsController@fetchUsers');
    Route::post('/createUser', 'SettingsController@createUser');
    Route::put('/updateUser', 'SettingsController@updateUser');
    Route::put('/updatePassword', 'SettingsController@updatePassword');
    Route::delete('/deleteUser/{id}', 'SettingsController@deleteUser');
    Route::post('/changePassword', 'SettingsController@changePassword');
});

Route::prefix('users')->namespace('Users')->group(function () {
    Route::post('/create', 'UserController@create');
    Route::delete('/delete/{id}', 'UserController@delete');
    Route::get('/fetch', 'UserController@fetch');
    Route::get('/fetchAll', 'UserController@fetchAll');
    Route::put('/update', 'UserController@update');
    Route::get('/user_csv', 'UserController@export_csv');
    Route::get('/getUserInfo', 'UserController@getUserInfo');
    Route::get('/getUserInfo', 'UserController@getUserInfo');
    Route::post('multiDelete', 'UserController@multiDelete');
});



Route::prefix('dept')->namespace('Dept')->group(function () {
    Route::post('/create', 'DepartmentController@create');
    Route::get('/fetch', 'DepartmentController@fetch');
    Route::get('/fetchAll', 'DepartmentController@fetchAll');
    Route::get('/fetchPOSDept', 'DepartmentController@fetchPOSDept');
    Route::get('/fetchAllDept', 'DepartmentController@fetchAllDept');

    Route::put('/update', 'DepartmentController@update');
    Route::delete('/destroy/{id}', 'DepartmentController@destroy');
});


Route::prefix('branch')->namespace('Branch')->group(function () {
    Route::post('/create', 'BranchController@create');
    Route::get('/fetch', 'BranchController@fetch');
    Route::post('/update', 'BranchController@update');
    Route::delete('/destroy/{id}', 'BranchController@destroy');
    Route::post('/visitBranch', 'BranchController@visitBranch');
});
Route::prefix('product_category')->namespace('Category')->group(function () {
    Route::post('/create', 'ProductCategoryController@create');
    Route::get('/fetch', 'ProductCategoryController@fetch');
    Route::get('/fetchAll', 'ProductCategoryController@fetchAll');
    Route::post('/update', 'ProductCategoryController@update');
    Route::delete('/destroy/{id}', 'ProductCategoryController@destroy');
});
Route::prefix('unit')->namespace('Unit')->group(function () {
    Route::post('/create', 'UnitController@create');
    Route::get('/fetch', 'UnitController@fetch');
    Route::get('/fetchAll', 'UnitController@fetchAll');
    Route::put('/update', 'UnitController@update');
    Route::delete('/destroy/{id}', 'UnitController@destroy');
});


Route::prefix('menu_category')->namespace('Menu')->group(function () {
    Route::post('/create', 'MenuCategoryController@create');
    Route::get('/fetch', 'MenuCategoryController@fetch');

    Route::put('/update', 'MenuCategoryController@update');
    Route::delete('/destroy/{id}', 'MenuCategoryController@destroy');
});


Route::prefix('stock')->namespace('Stock')->group(function () {
    Route::post('/create', [StockController::class, 'create']);
    Route::get('/fetch', [StockController::class, 'fetch']);
    Route::get('/fetchAll', [StockController::class, 'fetchAll']);
    Route::get('/fetchPos', [StockController::class, 'fetchPos']);
    Route::post('/update', [StockController::class, 'update']);
    Route::delete('/destroy/{id}', [StockController::class, 'destroy']);
    Route::delete('/delete_img/{id}', [StockController::class, 'delete_img']);
    Route::delete('/destroy_permanent/{id}', [StockController::class, 'destroy_permanent']);

    Route::get('/searchItem', [StockController::class, 'searchItem']);
    Route::get('/searchItempPriceGroup', [StockController::class, 'searchItempPriceGroup']);
    Route::get('/searchItemGeneric', [StockController::class, 'searchItemGeneric']);

    Route::put('/stock_take', [StockController::class, 'stock_take']);
    Route::put('/deduct_stock', [StockController::class, 'deduct_stock']);
    Route::get('/search_items', [StockController::class, 'search_items']);

    Route::post('/update_stock_cost', [StockController::class, 'update_stock_cost']);
    Route::post('/bulkImageStatusUpdate', [StockController::class, 'bulkImageStatusUpdate']);
    Route::get('/fetchStockTake', [StockController::class, 'fetchStockTake']);
    Route::get('/fetchTrashed', [StockController::class, 'fetchTrashed']);
    Route::post('/restoreTrashed', [StockController::class, 'restoreTrashed']);
    Route::post('/import', [ImportStockExcelController::class, 'import']);
    Route::post('/importNormal', [ImportStockExcelController::class, 'importNormal']);
    Route::post('/importEditEtims', [ImportStockExcelController::class, 'importEditEtims']);
    Route::post('/importInventory', [ImportStockExcelController::class, 'importInventory']);
    Route::post('/importStockCode', [ImportStockExcelController::class, 'importStockCode']);

    Route::delete('/deleteAllStock', [StockController::class, 'deleteAllStock']);
    Route::get('/searchShelfSortItemGeneric', [StockController::class, 'searchShelfSortItemGeneric']);
    Route::get('/searchPriceGroup', [StockController::class, 'searchPriceGroup']);
    Route::get('/fetchStockSheet', [StockController::class, 'fetchStockSheet']);

    Route::get('/fetchStockTakeItems', [StockController::class, 'fetchStockTakeItems']);
});
Route::prefix('split_settings')->namespace('Stock')->group(function () {
    Route::post('/create', 'SplitProductSettingController@create');
    Route::get('/fetch/{id}', 'SplitProductSettingController@fetch');
    Route::post('/destroy', 'SplitProductSettingController@destroy');
});

Route::prefix('split_product')->namespace('Stock')->group(function () {
    Route::post('/create', 'SplitProductController@create');
    Route::get('/fetch', 'SplitProductController@fetch');
});

Route::prefix('ingredient')->namespace('Ingredient')->group(function () {
    Route::post('/create', 'IngredientController@create');
    Route::get('/fetch/{id}', 'IngredientController@fetch');

    Route::put('/update', 'IngredientController@update');
    Route::delete('/destroy/{id}', 'IngredientController@destroy');
});

Route::prefix('suppliers')->namespace('Supplier')->group(function () {
    Route::post('/create', 'SupplierController@create');
    Route::get('/fetch', 'SupplierController@fetch');
    Route::get('/fetchAll', 'SupplierController@fetchAll');
    Route::put('/update', 'SupplierController@update');
    Route::delete('/destroy/{id}', 'SupplierController@destroy');
    Route::post('/import', 'SupplierController@import');
});


Route::prefix('customers')->namespace('Customer')->group(function () {
    Route::post('/create', 'CustomerController@create');
    Route::get('/fetch', 'CustomerController@fetch');
    Route::get('/fetchAll', 'CustomerController@fetchAll');
    Route::put('/update', 'CustomerController@update');
    Route::delete('/destroy/{id}', 'CustomerController@destroy');
    Route::get('/getCustomerSelect', 'CustomerController@getCustomerSelect');
    Route::post('/import', 'CustomerController@import');
    Route::get('/searchCustomer', 'CustomerController@searchCustomer');
    Route::post('/getRedeemablePoints', [CustomerController::class, 'getRedeemablePoints']);
    Route::get('/getCustomerBalance', [CustomerController::class, 'getCustomerBalance']);
});


Route::prefix('po')->namespace('PurchaseOrder')->group(function () {
    Route::post('/create', 'PurchaseOrderController@create');
    Route::post('/completeOrder', 'PurchaseOrderController@completeOrder');
    Route::get('/fetch', 'PurchaseOrderController@fetch');
    Route::get('/fetchUnconfirmed', 'PurchaseOrderController@fetchUnconfirmed');
    Route::get('/fetchAll', [PurchaseOrderController::class, 'fetchAll']);

    Route::get('/destroy', 'PurchaseOrderController@destroy');

    Route::delete('/deleteOrder/{id}', 'PurchaseOrderController@deleteOrder');

    Route::get('/fetchOrderNo', 'PurchaseOrderController@fetchOrderNo');
    Route::get('/fetchPendingOrder', 'PurchaseOrderController@fetchPendingOrder');

    Route::get('/fetchByOrder', 'PurchaseOrderController@fetchByOrder');
    Route::get('/approveOrder', 'PurchaseOrderController@approveOrder');
    Route::get('/vendorApproval', 'PurchaseOrderController@vendorApproval');
    Route::get('/downloadPO/{order_no}/{supplier_id}', 'PurchaseOrderController@downloadPO');
    Route::get('/fetchTotals', 'PurchaseOrderController@fetchTotals');
});

Route::prefix('direct_po')->namespace('PurchaseOrder')->group(function () {
    Route::post('/create', 'DirectPurchaseOrderController@create');
    Route::post('/completeOrder', 'DirectPurchaseOrderController@completeOrder');
    Route::get('/fetchByOrder', 'DirectPurchaseOrderController@fetchByOrder');
    Route::get('/fetchOrderNo', 'DirectPurchaseOrderController@fetchOrderNo');
    Route::get('/fetch', 'DirectPurchaseOrderController@fetch');
    Route::post('/approveOrder', 'DirectPurchaseOrderController@approveOrder');
    Route::post('/disapproveOrder', 'DirectPurchaseOrderController@disapproveOrder');
    Route::get('/fetchAll', 'DirectPurchaseOrderController@fetchAll');
    Route::delete('/deleteOrder/{id}', 'DirectPurchaseOrderController@deleteOrder');
    Route::get('/destroy', 'DirectPurchaseOrderController@destroy');
    Route::get('/downloadPO/{order_no}', 'DirectPurchaseOrderController@downloadPO');
    Route::get('/fetchPendingOrder', 'DirectPurchaseOrderController@fetchPendingOrder');
});
Route::prefix('tax')->namespace('Tax')->group(function () {
    Route::post('/create', 'TaxController@create');
    Route::put('/update', 'TaxController@update');
    Route::get('/fetch', 'TaxController@fetch');
});
Route::prefix('po_receivable')->namespace('PurchaseOrder')->group(function () {
    Route::post('/create', [POReceivableController::class, 'create']);
    Route::get('/fetchPendingDelivery', [POReceivableController::class, 'fetchPendingDelivery']);
    Route::get('/fetchOrders', [POReceivableController::class, 'fetchOrders']);
    Route::get('/destroy', [POReceivableController::class, 'destroy']);
    Route::post('/completeRecevable', [POReceivableController::class, 'completeRecevable']);
    Route::post('/downloadDN', [POReceivableController::class, 'downloadDN']);
    Route::post('/completeDelivery', 'POReceivableController@completeDelivery');
    Route::get('/fetchDeliveryNotes', 'POReceivableController@fetchDeliveryNotes');
    Route::get('/fetchDeliveryNo', 'POReceivableController@fetchDeliveryNo');
    Route::get('/fetchDeliveryDetails', 'POReceivableController@fetchDeliveryDetails');
    Route::post('/deleteDeliveryItem', 'POReceivableController@deleteDeliveryItem');
    Route::get('/fetchDeliveryDetails', 'POReceivableController@fetchDeliveryDetails');
    Route::get('/fetchDeliveryTotals', 'POReceivableController@fetchDeliveryTotals');
    Route::post('/receiveAllDeliveries', 'POReceivableController@receiveAllDeliveries');
    Route::get('/updateVat', 'POReceivableController@updateVat');
    Route::get('/fetchItemizedGrn', 'POReceivableController@fetchItemizedGrn');
    Route::get('/fetchItemizedGrnBatch', [POReceivableController::class, 'fetchItemizedGrnBatch']);
    Route::post('/submitUpdateBatchQty', 'POReceivableController@submitUpdateBatchQty');
    Route::post('/submitUpdateBatchSoldQty', 'POReceivableController@submitUpdateBatchSoldQty');
    Route::post('/addBatchItems', 'POReceivableController@addBatchItems');
    Route::get('/getAveragePurchasePrice', 'POReceivableController@getAveragePurchasePrice');
    Route::get('/purchaseAging', [POReceivableController::class, 'purchaseAging']);
});
Route::prefix('direct_po_receivable')->namespace('PurchaseOrder')->group(function () {
    Route::post('/create', 'DirectPurchaseController@create');
    Route::get('/fetchPendingDelivery', 'DirectPurchaseController@fetchPendingDelivery');
    Route::get('/fetchOrders', 'DirectPurchaseController@fetchOrders');
    Route::get('/destroy', 'DirectPurchaseController@destroy');
    Route::post('/completeRecevable', 'DirectPurchaseController@completeRecevable');
    Route::post('/downloadDN', 'DirectPurchaseController@downloadDN');
    Route::post('/receiveAll', 'DirectPurchaseController@receiveAll');

    Route::get('/fetchDPOrderReceivable', 'DirectPurchaseController@fetchDPOrderReceivable');
    Route::get('/fetchDeliveryNotes', 'DirectPurchaseController@fetchDeliveryNotes');
    Route::get('/fetchDeliveryNo', 'DirectPurchaseController@fetchDeliveryNo');
    Route::get('/downloadDetailsPdf', 'DirectPurchaseController@downloadDetailsPdf');
    Route::get('/fetchDirectPurchaseGRN', 'DirectPurchaseController@fetchDirectPurchaseGRN');
});
Route::prefix('requisition')->namespace('Requisition')->group(function () {
    Route::post('/create', 'RequisitionController@create');
    Route::post('/downloadReq', 'RequisitionController@downloadReq');
    Route::put('/update', 'RequisitionController@update');
    Route::get('/fetchReqNo', 'RequisitionController@fetchReqNo');
    Route::get('/fetch', 'RequisitionController@fetch');
    Route::get('/fetchPendngReq', 'RequisitionController@fetchPendngReq');
    Route::delete('/deleteItem/{id}', 'RequisitionController@deleteItem');
    Route::delete('/delete/{req_id}', 'RequisitionController@delete');
    Route::get('/fetchByReqId', 'RequisitionController@fetchByReqId');
    Route::post('/approve', 'RequisitionController@approve');
    Route::put('/updateReqData', 'RequisitionController@updateReqData');
    Route::get('/fetchUserRequests', 'RequisitionController@fetchUserRequests');
});

Route::prefix('store_requisition')->namespace('Requisition')->group(function () {
    Route::post('/create', 'StoreRequisitionController@create');
    Route::get('/fetch', 'StoreRequisitionController@fetch');
    Route::get('/fetchPendngReq', 'StoreRequisitionController@fetchPendngReq');
    Route::delete('/deleteItem/{id}', 'StoreRequisitionController@deleteItem');
    Route::delete('/destroy/{req_id}', 'StoreRequisitionController@destroy');
    // 
    Route::put('/updateReqData', 'StoreRequisitionController@updateReqData');
    Route::post('/issueItem', 'StoreRequisitionController@issueItem');
    Route::post('/completeReq', 'StoreRequisitionController@completeReq');

    Route::get('/report', 'StoreRequisitionController@report');
});


Route::prefix('assembly_requisition')->namespace('Assembly')->group(function () {
    Route::post('/create', 'AssemblyRequisitionController@create');
    Route::get('/fetchPendngReq', 'AssemblyRequisitionController@fetchPendngReq');
    Route::delete('/deleteItem/{id}', 'AssemblyRequisitionController@deleteItem');
    Route::post('/downloadReq', 'AssemblyRequisitionController@downloadReq');
    Route::get('/fetchRawMaterials', 'AssemblyRequisitionController@fetchRawMaterials');
    Route::get('/fetch', 'AssemblyRequisitionController@fetch');
    Route::get('/fetchByReqId', 'AssemblyRequisitionController@fetchByReqId');
    Route::post('/approve', 'AssemblyRequisitionController@approve');
    Route::delete('/delete/{req_id}', 'AssemblyRequisitionController@delete');
    Route::get('/fetchUserRequests', 'AssemblyRequisitionController@fetchUserRequests');
    Route::get('/fetchGroupedUserRequests', 'AssemblyRequisitionController@fetchGroupedUserRequests');
});


Route::prefix('dispatch_req')->namespace('DispatchReq')->group(function () {
    Route::post('/create', 'DispatchReqController@create');
    Route::get('/fetchDispatchReport', 'DispatchReqController@fetchDispatchReport');
});

Route::prefix('dispatch_assembly_req')->namespace('AssemblyDispatch')->group(function () {
    Route::post('/create', 'AssemblyDispatchController@create');
    Route::post('/update_assembly_stock_qty', 'AssemblyDispatchController@update_assembly_stock_qty');
    Route::get('/fetchDispatchReport', 'AssemblyDispatchController@fetchDispatchReport');
    Route::get('/fetchManufactureProducts', 'AssemblyDispatchController@fetchManufactureProducts');
});


Route::prefix('stock_returns')->namespace('StockReturn')->group(function () {
    Route::post('/create', 'StockReturnController@create');
    Route::put('/update', 'StockReturnController@update');
    Route::post('/destroy', 'StockReturnController@destroy');
    Route::get('/fetch', 'StockReturnController@fetch');
    Route::get('/fetchCode', 'StockReturnController@fetchCode');
});


Route::prefix('purchase_returns')->namespace('PurchaseReturn')->group(function () {
    Route::post('/create', 'PurchaseReturnController@create');
    Route::put('/update', 'PurchaseReturnController@update');
    Route::post('/destroy/{id}', 'PurchaseReturnController@destroy');
    Route::get('/fetch', 'PurchaseReturnController@fetch');
    Route::get('/fetchCode', 'PurchaseReturnController@fetchCode');
    Route::get('/fetchPendingReturns', 'PurchaseReturnController@fetchPendingReturns');
    Route::post('/downloadReturns', 'PurchaseReturnController@downloadReturns');
});

Route::prefix('orders')->namespace('Cart')->group(function () {
    Route::post('/create', 'CartController@create');
    Route::post('/orders', 'CartController@orders');

    Route::put('/update', 'CartController@update');
    Route::post('/destroy', 'CartController@destroy');
    Route::post('/cancelOrders', 'CartController@cancelOrders');
    Route::post('/recallOrder', 'CartController@recallOrder');
});
Route::prefix('sales')->namespace('Sale')->group(function () {

    Route::post('/create', 'SaleController@create');
    Route::get('/fetch', 'SaleController@fetch');
    Route::get('/fetchSaleDetailsWithItems', 'SaleController@fetchSaleDetailsWithItems');
    Route::get('/fetchAll', 'SaleController@fetchAll');
    Route::put('/update', 'SaleController@update');
    Route::delete('/destroy/{id}', 'SaleController@destroy');
    Route::get('/orderNumber/{id}', 'SaleController@orderNumber');
    Route::get('/fetchSaleDetails', 'SaleController@fetchSaleDetails');
    Route::post('/orders', 'SaleController@orders');
    Route::post('/updateOrder', 'SaleController@updateOrder');
    Route::post('/cancelOrders', 'SaleController@cancelOrders');
    Route::post('/deleteOrderItem', 'SaleController@deleteOrderItem');
    Route::get('/fetchSoldReceiptOrder', 'SaleController@fetchSoldReceiptOrder');
});


Route::prefix('invoices')->namespace('Invoice')->group(function () {
    Route::post('/create', 'InvoiceController@create');
    Route::post('/directInvoice', 'InvoiceController@directInvoice');
    Route::get('/orders/{order_no}', 'InvoiceController@orders');
    Route::get('/invoiceItems/{invoice_no}', 'InvoiceController@invoiceItems');
    Route::get('/fetch', 'InvoiceController@fetch');
    Route::get('/fetchTotals', 'InvoiceController@fetchTotals');

    Route::get('/fetchAll', 'InvoiceController@fetchAll');
    Route::get('/generateInvoice', 'InvoiceController@generateInvoice');
    Route::get('/fetchUnpaidInvoices', 'InvoiceController@fetchUnpaidInvoices');
    Route::post('/cancelInvoice', 'InvoiceController@cancelInvoice');
    Route::get('/fetchCustomerGroupedInvoiceTotals', 'InvoiceController@fetchCustomerGroupedInvoiceTotals');
    Route::get('/fetchexecutiveProductSalesReport', 'InvoiceController@fetchexecutiveProductSalesReport');
    Route::get('/fetchexecutiveCummulativeProductSalesReport', 'InvoiceController@fetchexecutiveCummulativeProductSalesReport');
    Route::get('/fetchGroupedLocationTotalsReport', 'InvoiceController@fetchGroupedLocationTotalsReport');
    Route::get('/fetchGroupedUserTotalsReport', 'InvoiceController@fetchGroupedUserTotalsReport');
    Route::get('/fetchGroupedCategory', 'InvoiceController@fetchGroupedCategory');
    Route::get('/groupedDeptTotals', 'InvoiceController@groupedDeptTotals');
    Route::post('/saveNotes', 'InvoiceController@saveNotes');
    Route::get('/saveAndDownloadInvoice', 'InvoiceController@saveAndDownloadInvoice');
    Route::get('/getInvoicePrint', 'InvoiceController@getInvoicePrint');
    Route::get('/profitLossInvoiceSummary', 'InvoiceController@profitLossInvoiceSummary');
    Route::get('/profitLossTotals', 'InvoiceController@profitLossTotals');
    Route::get('/scopeWithStockSummary', 'InvoiceController@scopeWithStockSummary');
    Route::get('/itemizedReport', 'InvoiceController@itemizedReport');
});



Route::prefix('delivery')->namespace('Delivery')->group(function () {
    Route::post('/fetchByInvoice', 'DeliveryController@fetchByInvoice');
    Route::post('/create', 'DeliveryController@create');
    Route::post('/invoiceDeliveryNotes', 'DeliveryController@invoiceDeliveryNotes');
    Route::get('/downLoadNote/{delivery_no}', 'DeliveryController@downLoadNote');
    Route::delete('/destroy/{delivery_no}', 'DeliveryController@destroy');
    Route::get('/fetchAll', 'DeliveryController@fetchAll');
    Route::get('/fetchByDnote', 'DeliveryController@fetchByDnote');
});

Route::prefix('cust_ledger')->namespace('CustomerLedger')->group(function () {
    Route::post('/create', [CustomerLedgerController::class, 'create']);
    Route::post('/import', [CustomerLedgerController::class, 'import']);
    Route::get('/customerLedgerStatement', [CustomerLedgerController::class, 'customerLedgerStatement']);
    Route::get('/fetchCustomerStatement', [CustomerLedgerController::class, 'fetchCustomerStatement']);
    Route::delete('/destroy/{id}', [CustomerLedgerController::class, 'destroy']);
    Route::delete('/deleteStatement/{id}', [CustomerLedgerController::class, 'deleteStatement']);
    Route::get('/fetchAll', [CustomerLedgerController::class, 'fetchAll']);
    Route::get('/generateCusStatment', [CustomerLedgerController::class, 'generateCusStatment']);
    Route::get('/getExcell', [CustomerLedgerController::class, 'getExcell']);
    Route::get('/generateCustTotal', [CustomerLedgerController::class, 'generateCustTotal']);
    Route::post('/getAmountOwed', [CustomerLedgerController::class, 'getAmountOwed']);
    Route::get('/getAgingReport', [CustomerLedgerController::class, 'getAgingReport']);
    Route::get('/exportAgingReport', [CustomerLedgerController::class, 'exportAgingReport']);
    Route::get('/fetchPrintStatement', [CustomerLedgerController::class, 'fetchPrintStatement']);
});

Route::prefix('payments')->namespace('CustomerPayment')->group(function () {
    Route::post('/create', [CustomerPaymentController::class, 'create']);
    Route::get('/fetch', [CustomerPaymentController::class, 'fetch']);
    Route::get('/fetchTotals', [CustomerPaymentController::class, 'fetchTotals']);
    Route::get('/fetchAll', [CustomerPaymentController::class, 'fetchAll']);
    Route::put('/update', [CustomerPaymentController::class, 'update']);
    Route::delete('/destroy/{id}', [CustomerPaymentController::class, 'destroy']);
    Route::post('/getAmountOwed', [CustomerPaymentController::class, 'getAmountOwed']);
    Route::post('/getInvoiceAmountOwed', [CustomerPaymentController::class, 'getInvoiceAmountOwed']);

    Route::get('/fetchCustomerBalances', [CustomerPaymentController::class, 'fetchCustomerBalances']);
    Route::post('/fetchCustomerStatement', [CustomerPaymentController::class, 'fetchCustomerStatement']);
    Route::post('/downloadStatement', [CustomerPaymentController::class, 'downloadStatement']);
});


Route::prefix('transaction')->namespace('Transaction')->group(function () {

    Route::get('/fetch', 'TransactionController@fetch');
});


Route::prefix('supplier_invoice')->namespace('SupplierInvoice')->group(function () {
    Route::post('/create', [SupplierInvoiceController::class, 'create']);
    Route::post('/save_particulars', [SupplierInvoiceController::class, 'save_particulars']);
    Route::get('/fetch', [SupplierInvoiceController::class, 'fetch']);
    Route::post('/invoiceParticulars', [SupplierInvoiceController::class, 'invoiceParticulars']);
    Route::post('/destroy', [SupplierInvoiceController::class, 'destroy']);
    Route::get('/fetchSupplierCredit', [SupplierInvoiceController::class, 'fetchSupplierCredit']);

    Route::delete('/destroy_particular/{id}', 'SupplierInvoiceController@destroy_particular');
    Route::get('/fetchAll', 'SupplierInvoiceController@fetch');
    Route::get('/fetchGroupedInvoices', 'SupplierInvoiceController@fetchGroupedInvoices');
    Route::get('/fetchInvoice', 'SupplierInvoiceController@fetchInvoice');
    Route::get('/fetchAllInvoice', 'SupplierInvoiceController@fetchAllInvoice');
    Route::get("/fetchTotalBalances", 'SupplierInvoiceController@fetchTotalBalances');
    Route::get("/fetchUnpaidSupplierInvoices", 'SupplierInvoiceController@fetchUnpaidSupplierInvoices');
    Route::get('/fetchTotal', 'SupplierInvoiceController@fetchTotal');
    Route::post('/importOpeningBalance', 'SupplierInvoiceController@importOpeningBalance');
});

Route::prefix('supplier_payment')->namespace('SupplierPayment')->group(function () {
    Route::post('/create', 'SupplierPaymentController@create');
    Route::get('/fetch_details/{id}', 'SupplierPaymentController@fetch_details');
    Route::get('/fetchSupplierPaymentReport', 'SupplierPaymentController@fetchSupplierPaymentReport');
    Route::get('/exportExcel', 'SupplierPaymentController@exportExcel');
    Route::get('/fetchInvoiceSupplierPaymentTotal', 'SupplierPaymentController@fetchInvoiceSupplierPaymentTotal');
    Route::get('/fetchInvoiceSupplierPaymentReport', 'SupplierPaymentController@fetchInvoiceSupplierPaymentReport');
});

Route::prefix('supp_ledger')->namespace('Creditors')->group(function () {
    Route::get('/fetchSupplierStatement', [CreditorsController::class, 'fetchSupplierStatement']);
    Route::post('/create', [CreditorsController::class, 'create']);
    Route::post('/addBalances', [CreditorsController::class, 'addBalances']);
    Route::post('/import', [CreditorsController::class, 'import']);
    Route::get('/fetchAll', [CreditorsController::class, 'fetchAll']);
    Route::get('/generateStatment', [CreditorsController::class, 'generateStatment']);
    Route::get('/getExcell', [CreditorsController::class, 'getExcell']);
    Route::delete('/destroy/{id}', [CreditorsController::class, 'destroy']);
    Route::get('/getAgingReport', [CreditorsController::class, 'getAgingReport']);
    Route::get('/exportAgingReport', [CreditorsController::class, 'exportAgingReport']);
    Route::get('/fetchStatementExcel', [CreditorsController::class, 'fetchStatementExcel']);
});

Route::prefix('credit')->namespace('CreditNote')->group(function () {
    Route::post('/create', [CreditNoteController::class, 'create']);
    Route::delete('/destroy/{id}', [CreditNoteController::class, 'destroy']);
    Route::get('/fetchAll', [CreditNoteController::class, 'fetch']);
    Route::get('/fetch', [CreditNoteController::class, 'fetch']);
    Route::get('/getByInvoiceNo/{invoice_no}', [CreditNoteController::class, 'getByInvoiceNo']);
    Route::get('/creditItems/{invoice_no}', [CreditNoteController::class, 'creditItems']);
    Route::post('/approve', [CreditNoteController::class, 'approve']);
    Route::post('/cancel', [CreditNoteController::class, 'cancel']);
    Route::get('/fetchByCreditNo/{credit_no}', [CreditNoteController::class, 'fetchByCreditNo']);
    Route::get('/generateCredit/{credit_no}', [CreditNoteController::class, 'generateCredit']);
    Route::post('/saveNotes', [CreditNoteController::class, 'saveNotes']);
});



Route::prefix('employee_type')->namespace('EmployeeType')->group(function () {
    Route::post('/create', 'EmployeeTypeController@create');
    Route::get('/fetch', 'EmployeeTypeController@fetch');
    Route::put('/update', 'EmployeeTypeController@update');
    Route::delete('/destroy/{id}', 'EmployeeTypeController@destroy');
});


Route::prefix('employee')->namespace('Employee')->group(function () {
    Route::post('/create', 'EmployeeController@create');
    Route::get('/fetch', 'EmployeeController@fetch');
    Route::get('/getAll', 'EmployeeController@fetch');
    Route::get('/getPieceWorkers', 'EmployeeController@getPieceWorkers');
    Route::get('/getCasuals', 'EmployeeController@getCasuals');
    Route::post('/import', 'EmployeeController@import');
    Route::put('/update', 'EmployeeController@update');
    Route::delete('/destroy/{id}', 'EmployeeController@destroy');
    Route::get('/getById/{id}', 'EmployeeController@getById');
});


Route::prefix('production')->namespace('Production')->group(function () {
    Route::post('/create', 'ProductionController@create');
    Route::get('/fetch', 'ProductionController@fetch');
    Route::get('/getAllProduction', 'ProductionController@fetch');
    Route::put('/update', 'ProductionController@update');
    Route::delete('/destroy/{id}', 'ProductionController@destroy');
});

Route::prefix('products')->namespace('Product')->group(function () {
    Route::post('/create', 'ProductController@create');
    Route::get('/fetch', 'ProductController@fetch');
    Route::get('/getAllProducts', 'ProductController@fetch');
    Route::get('/getProdutSelect', 'ProductController@getProdutSelect');

    Route::put('/update', 'ProductController@update');
    Route::delete('/destroy/{id}', 'ProductController@destroy');
    Route::post('/fetchById', 'ProductController@fetchById');
    Route::post('/searchProducts', 'ProductController@searchProducts');
    Route::get('/searchItem', 'ProductController@searchItem');
});

Route::prefix('time_logs')->namespace('TimeLog')->group(function () {
    Route::post('/create', 'TimeLogController@create');
    Route::get('/fetch', 'TimeLogController@fetch');
    Route::get('/fetchAll', 'TimeLogController@fetch');
    Route::put('/update', 'TimeLogController@update');
    Route::delete('/destroy/{id}', 'TimeLogController@destroy');
});

Route::prefix('casuals_timelogs')->namespace('Casuals')->group(function () {
    Route::post('/create', 'CasualsController@create');
    Route::get('/fetch', 'CasualsController@fetch');
    Route::get('/fetchAll', 'CasualsController@fetch');
    Route::put('/update', 'CasualsController@update');
    Route::delete('/destroy/{id}', 'CasualsController@destroy');
});



Route::prefix('loans')->namespace('Loan')->group(function () {
    Route::post('/create', 'LoanController@create');
    Route::post('/createCoLoans', 'LoanController@createCoLoans');
    Route::get('/fetch', 'LoanController@fetch');
    Route::get('/fetchByUser', 'LoanController@fetchByUser');
    Route::get('/fetchAll', 'LoanController@fetch');
    Route::put('/update', 'LoanController@update');
    Route::delete('/destroy/{id}', 'LoanController@destroy');
});


Route::prefix('allowance')->namespace('Allowance')->group(function () {
    Route::post('/create', 'AllowanceController@create');
    Route::get('/fetch', 'AllowanceController@fetch');
    Route::get('/fetchAll', 'AllowanceController@fetch');
    Route::put('/update', 'AllowanceController@update');
    Route::delete('/destroy/{id}', 'AllowanceController@destroy');
});


Route::prefix('deductions')->namespace('Deduction')->group(function () {
    Route::post('/create', 'DeductionConroller@create');
    Route::get('/fetch', 'DeductionConroller@fetch');
    Route::get('/fetchAll', 'DeductionConroller@fetch');
    Route::put('/update', 'DeductionConroller@update');
    Route::delete('/destroy/{id}', 'DeductionConroller@destroy');
});


Route::prefix('statutory')->namespace('Statutory')->group(function () {
    Route::post('/create', 'StatutoryController@create');
    Route::get('/fetch', 'StatutoryController@fetch');
    Route::get('/fetchAll', 'StatutoryController@fetch');
    Route::put('/update', 'StatutoryController@update');
    Route::delete('/destroy/{id}', 'StatutoryController@destroy');
});


Route::prefix('advance')->namespace('Advance')->group(function () {
    Route::post('/create', 'AdvanceController@create');
    Route::get('/fetch', 'AdvanceController@fetch');
    Route::get('/fetchTotals', 'AdvanceController@fetchTotals');

    Route::get('/fetchAll', 'AdvanceController@fetch');
    Route::put('/update', 'AdvanceController@update');
    Route::delete('/destroy/{id}', 'AdvanceController@destroy');
});

Route::prefix('commission')->namespace('Commission')->group(function () {
    Route::post('/create', 'CommissionController@create');
    Route::get('/fetch', 'CommissionController@fetch');
    Route::get('/fetchAll', 'CommissionController@fetch');
    Route::put('/update', 'CommissionController@update');
    Route::delete('/destroy/{id}', 'CommissionController@destroy');
});

Route::prefix('payroll')->namespace('Payroll')->group(function () {
    Route::post('/create', 'PayrollController@create');
    Route::post('/createPieceWorkPayslips', 'PayrollController@createPieceWorkPayslips');
    Route::post('/createCasualsPayslips', 'PayrollController@createCasualsPayslips');
    Route::get('/fetch', 'PayrollController@fetch');
    Route::get('/fetchGrouped', 'PayrollController@fetchGrouped');
    Route::get('/fetchAll', 'PayrollController@fetchAll');
    Route::put('/update', 'PayrollController@update');
    Route::delete('/destroy/{id}', 'PayrollController@destroy');
    Route::post('/downLoadPayslip', 'PayrollController@downLoadPayslip');
    Route::post('/getEmployeesByPayMethod', 'PayrollController@getEmployeesByPayMethod');
    Route::post('/getMonthlyEmplByDept', 'PayrollController@getMonthlyEmplByDept');
});


Route::prefix('payslips')->namespace('Payslips')->group(function () {

    Route::get('/fetchGrouped', 'PaySlipsReportController@fetchGrouped');
    Route::get('/fetchAll', 'PaySlipsReportController@fetchAll');

    Route::get('/fetchPayslipById/{id}', 'PaySlipsReportController@fetchPayslipById');
    Route::get('/fetchAllById/{id}', 'PaySlipsReportController@fetchAllById');
    Route::get('/fetchSummarised', 'PaySlipsReportController@fetchSummarised');
    Route::post('/getPayslipsByMonth', 'PaySlipsReportController@getPayslipsByMonth');
    Route::post('/fetchAllPayslipsByMonth', 'PaySlipsReportController@fetchAllPayslipsByMonth');
    Route::get('/fetchAllSummarised', 'PaySlipsReportController@fetchAllSummarised');
    Route::get('/downLoadPayslip', 'PaySlipsReportController@downLoadPayslip');
    Route::get('/downloadP9', 'PaySlipsReportController@downloadP9');
});

Route::prefix('production_cost')->namespace('Stock')->group(function () {
    Route::post('/create', 'ProductionCostController@create');
    Route::get('/fetch/{id}', 'ProductionCostController@fetch');

    Route::put('/update', 'ProductionCostController@update');
    Route::delete('/destroy/{id}', 'ProductionCostController@destroy');
    Route::post('/updateProductionCost', 'ProductionCostController@updateProductionCost');
});

Route::prefix('room_standard')->namespace('Room')->group(function () {
    Route::post('/create', 'RoomStandardController@create');
    Route::get('/fetch', 'RoomStandardController@fetch');
    Route::put('/update', 'RoomStandardController@update');
    Route::delete('/destroy/{id}', 'RoomStandardController@destroy');
});
Route::prefix('rooms')->namespace('Room')->group(function () {
    Route::post('/create', 'RoomController@create');

    Route::get('/fetch', 'RoomController@fetch');
    Route::put('/update', 'RoomController@update');
    Route::delete('/destroy/{id}', 'RoomController@destroy');
    Route::post('/updateMaintenanceStatus', 'RoomController@updateMaintenanceStatus');
});

Route::prefix('room_package')->namespace('Room')->group(function () {
    Route::post('/create', 'RoomPackageController@create');
    Route::get('/fetch', 'RoomPackageController@fetch');
    Route::put('/update', 'RoomPackageController@update');
    Route::delete('/destroy/{id}', 'RoomPackageController@destroy');
});

Route::prefix('room_rate')->namespace('Room')->group(function () {
    Route::post('/create', 'RoomRateController@create');
    Route::get('/fetch', 'RoomRateController@fetch');
    Route::put('/update', 'RoomRateController@update');
    Route::delete('/destroy/{id}', 'RoomRateController@destroy');

    Route::post('/checkRate', 'RoomRateController@checkRate');
});



Route::prefix('location')->namespace('Location')->group(function () {
    Route::post('/create', 'LocationController@create');
    Route::get('/fetch', 'LocationController@fetch');
    Route::get('/fetchAll', 'LocationController@fetchAll');

    Route::put('/update', 'LocationController@update');
    Route::delete('/destroy/{id}', 'LocationController@destroy');
});

Route::prefix('table')->namespace('Table')->group(function () {
    Route::post('/create', 'TableController@create');
    Route::get('/fetch', 'TableController@fetch');
    Route::put('/update', 'TableController@update');
    Route::delete('/destroy/{id}', 'TableController@destroy');
});


Route::prefix('pos_order')->namespace('POSOrder')->group(function () {
    Route::post('/create', 'POSOrderController@create');
    Route::get('/fetch', 'POSOrderController@fetch');
    Route::get('/fetchDetails', 'POSOrderController@fetchDetails');
    Route::delete('/destroy/{order_no}', 'POSOrderController@destroy');
    Route::get('/fetchDeletedOrders', 'POSOrderController@fetchDeletedOrders');
});

Route::prefix('pos_sale')->namespace('Sale')->group(function () {
    Route::post('/create', [POSSaleController::class, 'create']);
    Route::post('/vscuEtimsSale', [POSSaleController::class, 'vscuEtimsSale']);

    Route::post('/settleCredit', [POSSaleController::class, 'settleCredit']);
    Route::get('/fetch', [POSSaleController::class, 'fetch']);
    Route::get('/groupedDeptTotals', [POSSaleController::class, 'groupedDeptTotals']);
    Route::get('/profitLossSummary', [POSSaleController::class, 'profitLossSummary']);
    Route::get('/profitLossTotals', [POSSaleController::class, 'profitLossTotals']);

    Route::put('/update', [POSSaleController::class, 'update']);
    Route::delete('/destroy/{id}', [POSSaleController::class, 'destroy']);
    Route::get('/salesReport', [POSSaleController::class, 'salesReport']);
    Route::get('/salesReportTotals', [POSSaleController::class, 'salesReportTotals']);
    Route::get('/getSaleDetails', [POSSaleController::class, 'getSaleDetails']);
    Route::get('/detailsReport', [POSSaleController::class, 'detailsReport']);
    Route::get('/dailySalesReport', [POSSaleController::class, 'dailySalesReport']);
    Route::get('/soldItemTotals', [POSSaleController::class, 'soldItemTotals']);
    Route::post('/completeCreditSale', [POSSaleController::class, 'completeCreditSale']);
    Route::post('/completeOrderByEmployeeDeduction', [POSSaleController::class, 'completeOrderByEmployeeDeduction']);
    Route::get('/fetchEmployeeSaleDeduction', [POSSaleController::class, 'fetchEmployeeSaleDeduction']);
    Route::get('/fetchGroupedCategory', [POSSaleController::class, 'fetchGroupedCategory']);
    Route::get('/fetchGroupedUser', [POSSaleController::class, 'fetchGroupedUser']);
    Route::get('/fetchAllSales', [POSSaleController::class, 'fetchAllSales']);
    Route::get('/fetchAllSaleTotal', [POSSaleController::class, 'fetchAllSaleTotal']);
    Route::get('/fetchAllEtimsReport', [POSSaleController::class, 'fetchAllEtimsReport']);
    Route::get('/fetchAllEtimsTotalReport', [POSSaleController::class, 'fetchAllEtimsTotalReport']);


    Route::get('/fetchEmployeeDeductionAsRefundable', [POSSaleController::class, 'fetchEmployeeDeductionAsRefundable']);
    Route::get('/fetchAccompanimentSales', [POSSaleController::class, 'fetchAccompanimentSales']);
    Route::get('/fetchUngroupedAccompanimentSales', [POSSaleController::class, 'fetchUngroupedAccompanimentSales']);
    Route::get('/fetchAccompanimentTotals', [POSSaleController::class, 'fetchAccompanimentTotals']);
    Route::get('/fetchSaleItems', [POSSaleController::class, 'fetchSaleItems']);
    Route::get('/fetchCardPaymentReport', [POSSaleController::class, 'fetchCardPaymentReport']);
    Route::get('/fetchCardPaymentTotalReport', [POSSaleController::class, 'fetchCardPaymentTotalReport']);
    Route::post('/deleteSale', [POSSaleController::class, 'deleteSale']);
    Route::post('/reverseEtimsSale', [POSSaleController::class, 'reverseEtimsSale']);

    Route::get('/saleSumaryWithActivity', [POSSaleController::class, 'saleSumaryWithActivity']);
    Route::get('/saleSumaryWithActivityExport', [POSSaleController::class, 'saleSumaryWithActivityExport']);

    Route::post('/etimsCompleteSale', [POSSaleController::class, 'etimsCompleteSale']);
    Route::post('/validateAndCompleteEtimsSale', [POSSaleController::class, 'validateAndCompleteEtimsSale']);
    Route::post('/etimsBackSale', [POSSaleController::class, 'etimsBackSale']);
    Route::post('/validateEtimsBackSale', [POSSaleController::class, 'validateEtimsBackSale']);
    Route::post('/validateEtimsCreditnote', [POSSaleController::class, 'validateEtimsCreditnote']);

    Route::post('/exchangeWithOriginalReceiptAndTopUpPayment', [POSSaleController::class, 'exchangeWithOriginalReceiptAndTopUpPayment']);
    Route::get('/combineStockSummary', [POSSaleController::class, 'combineStockSummary']);
    Route::get('/combineStockSummaryExcel', [POSSaleController::class, 'combineStockSummaryExcel']);
    Route::get('/fetchAllSalesArchived', [POSSaleController::class, 'fetchAllSalesArchived']);
    Route::get('/fetchArchivedSaleItems', [POSSaleController::class, 'fetchArchivedSaleItems']);
    Route::post('/checkEtimsStatusSale', [POSSaleController::class, 'checkEtimsStatusSale']);
    Route::post('/commitRefund', [POSSaleController::class, 'commitRefund']);
    Route::post('/postSalesetimsSale', [POSSaleController::class, 'postSalesetimsSale']);
    Route::post('/completePreoderSale', [POSSaleController::class, 'completePreoderSale']);
    Route::post('/completeDirectRetailExchangeSales', [POSSaleController::class, 'completeDirectRetailExchangeSales']);
    Route::get('/fetchSalesByReceiptNo', [POSSaleController::class, 'fetchSalesByReceiptNo']);
    Route::get('/profitLossDetails', [POSSaleController::class, 'profitLossDetails']);
    Route::get('/getDeadStock', [POSSaleController::class, 'getDeadStock']);
    Route::get("/searchCustomerSales", [POSSaleController::class, "searchCustomerSales"]);
    Route::get("/searchCustomerSalesItems", [POSSaleController::class, "searchCustomerSalesItems"]);
});

Route::prefix('shift')->namespace('Shift')->group(function () {
    Route::post('/create', 'ShiftController@create');
    Route::get('/fetch', 'ShiftController@fetch');
    Route::put('/update', 'ShiftController@update');
    Route::delete('/destroy/{id}', 'ShiftController@destroy');
});










Route::prefix('take_away')->namespace('TakeAway')->group(function () {
    Route::post('/create', 'TakeAwayOrderController@create');
    Route::get('/fetch', 'TakeAwayOrderController@fetch');
    Route::put('/update', 'TakeAwayOrderController@update');
    Route::delete('/destroy/{id}', 'TakeAwayOrderController@destroy');
    Route::delete('/destroyOrder/{order_no}', 'TakeAwayOrderController@destroyOrder');
    Route::post('/getPendingOrders', 'TakeAwayOrderController@getPendingOrders');
    Route::post('/crementOrder', 'TakeAwayOrderController@crementOrder');
    Route::post('/completeOrder', 'TakeAwayOrderController@completeOrder');
    Route::post('/addNote', 'TakeAwayOrderController@addNote');
    Route::post('/validateBill', 'TakeAwayOrderController@validateBill');
    Route::get('/fetchBills', 'TakeAwayOrderController@fetchBills');
    Route::get('/recallBill', 'TakeAwayOrderController@recallBill');

    Route::post('/updateOrderCustomer', 'TakeAwayOrderController@updateOrderCustomer');
    Route::get('/fetchOrders', 'TakeAwayOrderController@fetchOrders');
    Route::get('/getByOrder', 'TakeAwayOrderController@getByOrder');
});



Route::prefix('takeaway_sale')->namespace('Sale')->group(function () {
    Route::post('/create', 'TakeAwaySaleController@create');
    Route::get('/fetch', 'TakeAwaySaleController@fetch');
    Route::put('/update', 'TakeAwaySaleController@update');
    Route::delete('/destroy/{id}', 'TakeAwaySaleController@destroy');
    Route::get('/salesReport', 'TakeAwaySaleController@salesReport');
    Route::get('/salesReportTotals', 'TakeAwaySaleController@salesReportTotals');
    Route::get('/getSaleDetails', 'TakeAwaySaleController@getSaleDetails');
    Route::get('/detailsReport', 'TakeAwaySaleController@detailsReport');
    Route::get('/soldItemTotals', 'TakeAwaySaleController@soldItemTotals');
    Route::get('/salesReportTotals', 'TakeAwaySaleController@salesReportTotals');
    Route::get('/salesReport', 'TakeAwaySaleController@salesReport');
    Route::get('/getSaleDetails', 'TakeAwaySaleController@getSaleDetails');

    Route::post('/completeCreditSale', 'TakeAwaySaleController@completeCreditSale');
    Route::post('/completeOrderByEmployeeDeduction', 'TakeAwaySaleController@completeOrderByEmployeeDeduction');
});

Route::prefix('cashier_balance')->namespace('Cashier')->group(function () {
    Route::get('/showTotal', 'CashierBalanceController@showTotal');
    Route::post('/createOpeningBalance', 'CashierBalanceController@createOpeningBalance');
    Route::post('/createClosingBalance', 'CashierBalanceController@createClosingBalance');
    Route::delete('/destroy/{id}', 'CashierBalanceController@destroy');
    Route::get('/getOpeningTime', 'CashierBalanceController@getOpeningTime');
    Route::get('/fetch', "CashierBalanceController@fetch");
    Route::get('/fetchSum', "CashierBalanceController@fetchSum");
    Route::post('/closeShift', "CashierBalanceController@closeShift");
    Route::get('/closeShiftSales', "CashierBalanceController@closeShiftSales");
    Route::post('/fetchBalancesDetails', "CashierBalanceController@fetchBalancesDetails");
});

Route::prefix('internal_issue')->namespace('Stock')->group(function () {
    Route::post('/create', 'StockIssueController@create');
    Route::get('/fetchIssueNo', 'StockIssueController@fetchIssueNo');
    Route::post('/completeIssue', 'StockIssueController@completeIssue');
    Route::post('/destroy', 'StockIssueController@destroy');
    Route::get('/fetchIssueData', 'StockIssueController@fetchIssueData');
    Route::get('/downloadIssueNote/{issue_no}', 'StockIssueController@downloadIssueNote');
    Route::get('/fetchIssueDetailed', 'StockIssueController@fetchIssueDetailed');
    Route::get('/downloadDetailsPdf', 'StockIssueController@downloadDetailsPdf');
    Route::post('/kitchenBatchIssue', 'StockIssueController@kitchenBatchIssue');
    Route::get('/updateIssue', 'StockIssueController@updateIssue');
    Route::get('/fetchDispatchDetails', 'StockIssueController@fetchDispatchDetails');
});

Route::prefix('stock_movt')->namespace('Stock')->group(function () {
    Route::get('/fetch', 'StockMovementController@fetch');
    Route::get('/fetchAll', 'StockMovementController@fetchAll');
    Route::get('/fetchDeptStockMovt', 'StockMovementController@fetchDeptStockMovt');
    Route::get('/downloadPdf', 'StockMovementController@downloadPdf');
    Route::get('/downloadDeptPdfLogs', 'StockMovementController@downloadDeptPdfLogs');
    Route::get('/downloadPOSStockMovtPdf', 'StockMovementController@downloadPOSStockMovtPdf');
    Route::get('/downloadPdfLogs', 'StockMovementController@downloadPdfLogs');
    Route::post('/updatePhysicalStock', 'StockMovementController@updatePhysicalStock');
    Route::post('/updateDeptPhysicalStock', 'StockMovementController@updateDeptPhysicalStock');
    Route::get('/fetchMovtDeaprtmentLogs', 'StockMovementController@fetchMovtDeaprtmentLogs');
});
Route::prefix('stock_update')->namespace('Stock')->group(function () {
    Route::post('/create', 'StockUpdateController@create');
    Route::get('/fetch', 'StockUpdateController@fetch');

    Route::get('/fetchUpdateByTimesmtamp', [StockUpdateController::class, 'fetchUpdateByTimesmtamp']);

    Route::get('/fetchDecrementByTimesmtamp', [StockUpdateController::class, 'fetchDecrementByTimesmtamp']);
    Route::get('/fetchIncrementByTimesmtamp', [StockUpdateController::class, 'fetchIncrementByTimesmtamp']);
});


Route::prefix('reception_balance')->namespace('ReceptionBalance')->group(function () {
    Route::post('/createOpeningBalance', 'ReceptionBalanceController@createOpeningBalance');
    Route::post('/createClosingBalance', 'ReceptionBalanceController@createClosingBalance');
    Route::get('/showTotal', 'ReceptionBalanceController@showTotal');
    Route::get('/getOpeningTime', 'ReceptionBalanceController@getOpeningTime');
    Route::get('/fetch', "ReceptionBalanceController@fetch");
    Route::get('/fetchSum', "ReceptionBalanceController@fetchSum");
    Route::delete('/destroy/{id}', 'ReceptionBalanceController@destroy');
    Route::get('/closeShiftSales', 'ReceptionBalanceController@closeShiftSales');
    Route::post('/fetchBalancesDetails', 'ReceptionBalanceController@fetchBalancesDetails');
});

Route::prefix('accompaniment')->namespace('Stock')->group(function () {
    Route::post('/create', 'AccompanimentController@create');
    Route::get('/fetch', 'AccompanimentController@fetch');
    Route::delete('/destroy/{id}', 'AccompanimentController@destroy');
});

Route::prefix('conference')->namespace('Conference')->group(function () {
    Route::post('/create', 'ConferenceController@create');
    Route::get('/fetch', 'ConferenceController@fetch');
    Route::get('/fetchTotals', 'ConferenceController@fetchTotals');

    Route::delete('/destroy/{id}', 'ConferenceController@destroy');
    Route::get("/getReceiptNo", 'ConferenceController@getReceiptNo');
});
Route::prefix('void_items')->namespace('POSOrder')->group(function () {
    Route::post('/create', 'VoidReasonController@create');
    Route::put('/update', 'VoidReasonController@update');
    Route::get('/fetch', 'VoidReasonController@fetch');

    Route::delete('/destroy/{id}', 'VoidReasonController@destroy');
});
Route::prefix('room_transfer')->namespace('Reservation')->group(function () {

    Route::post('/update', 'RoomTransferController@update');
    Route::get('/fetch', 'RoomTransferController@fetch');

    Route::delete('/destroy/{id}', 'RoomTransferController@destroy');
});


Route::prefix('reservation_credit_note')->namespace('Reservation')->group(function () {

    Route::post('/create', 'ReservationCreditNoteController@create');
    Route::get('/fetch', 'ReservationCreditNoteController@fetch');
    Route::delete('/destroy/{id}', 'ReservationCreditNoteController@destroy');
    Route::get('/fetchCreditNoteNumber', 'ReservationCreditNoteController@fetchCreditNoteNumber');
});

Route::prefix('guest_co')->namespace('Guest')->group(function () {
    Route::post('/create', 'GuesCompanyController@create');
    Route::put('/update', 'GuesCompanyController@update');
    Route::get('/fetch', 'GuesCompanyController@fetch');
    Route::get('/getCompanyName/{id}', 'GuesCompanyController@getCompanyName');

    Route::delete('/destroy/{id}', 'GuesCompanyController@destroy');
});



Route::prefix('stockmvt')->namespace('Stock')->group(function () {
    Route::get('/fetchStockMovement', 'StockMovementMaintStoreController@fetchStockMovement');
    Route::get('/downloadPdf', 'StockMovementMaintStoreController@downloadPdf');

    Route::get('/fetchDeptStockMovt', 'StockMovementDepartmentReportController@fetchDeptStockMovt');
    Route::get('/fetchBlankStockSheet', 'StockMovementDepartmentReportController@fetchBlankStockSheet');
    Route::get('/fetchMovtStatus', 'StockMovementDepartmentReportController@fetchMovtStatus');
    Route::get('/fetchMovtStoreStatus', 'StockMovementMaintStoreController@fetchMovtStoreStatus');
});

Route::prefix('stockmvt')->namespace('Stock')->group(function () {
    Route::get('/fetchStockMovement', 'StockMovementMaintStoreController@fetchStockMovement');
    Route::get('/downloadPdf', 'StockMovementMaintStoreController@downloadPdf');

    Route::get('/fetchDeptStockMovt', 'StockMovementDepartmentReportController@fetchDeptStockMovt');
    Route::get('/fetchBlankStockSheet', 'StockMovementDepartmentReportController@fetchBlankStockSheet');
    Route::get('/fetchMovtStatus', 'StockMovementDepartmentReportController@fetchMovtStatus');
    Route::get('/fetchMovtStoreStatus', 'StockMovementMaintStoreController@fetchMovtStoreStatus');
});

Route::prefix('system_reports')->namespace('Reports')->group(function () {
    Route::get('/fetch', 'AllReportsController@fetch');
    Route::get('/fetchLogins', 'AllReportsController@fetchLogins');
});

Route::prefix('stock_production')->namespace('Stock')->group(function () {
    Route::post('/fetchRawMaterials', 'StockProductionController@fetchRawMaterials');
    Route::post('/completeProductIssue', 'StockProductionController@completeProductIssue');
    Route::get('/fetchIssueNo', 'StockProductionController@fetchIssueNo');
    Route::get('/productionNo', 'StockProductionController@productionNo');
    Route::get('/fetch', 'StockProductionController@fetch');
    Route::post('/completeProduction', 'StockProductionController@completeProduction');
    Route::get('/fetchOngoingProductionMaterials', 'StockProductionController@fetchOngoingProductionMaterials');
    Route::post('/updateRawMterials', 'StockProductionController@updateRawMterials');
    Route::get('/productionDetails', 'StockProductionController@productionDetails');
    Route::get('/fetchTotal', 'StockProductionController@fetchTotal');
    Route::get('/fetchTotalQtyProduced', 'StockProductionController@fetchTotalQtyProduced');
});
Route::prefix('stock_disassembly')->namespace('Stock')->group(function () {
    Route::post('/productDisassembly', 'StockDisassemblyController@productDisassembly');
    Route::get('/fetch', 'StockDisassemblyController@fetch');
    Route::get('/fetchDetails', 'StockDisassemblyController@fetchDetails');
});


Route::prefix('archive_order')->namespace('POSOrder')->group(function () {
    Route::post('/create', 'ArchivedOrderController@create');
    Route::post('/archiveOnline', 'ArchivedOrderController@archiveOnline');


    Route::get('/fetch', 'ArchivedOrderController@fetch');
    Route::get('/fetchItemized', 'ArchivedOrderController@fetchItemized');
    Route::get('/fetchGroupedItems', 'ArchivedOrderController@fetchGroupedItems');

    Route::get('/archivedTotal', 'ArchivedOrderController@archivedTotal');
    Route::get('/details', 'ArchivedOrderController@details');
});


Route::prefix('main_store_issue')->namespace('Stock')->group(function () {
    Route::post('/create', 'MainStoreIssueController@create');
    Route::get('/fetchIssueNo', 'MainStoreIssueController@fetchIssueNo');
    Route::post('/completeIssue', 'MainStoreIssueController@completeIssue');
    Route::delete('/destroy/{id}', 'MainStoreIssueController@destroy');
    Route::get('/fetchIssueData', 'MainStoreIssueController@fetchIssueData');
    Route::get('/downloadIssueNote/{issue_no}', 'MainStoreIssueController@downloadIssueNote');
    Route::get('/fetchIssueDetailed', 'MainStoreIssueController@fetchIssueDetailed');
    Route::get('/downloadDetailsPdf', 'MainStoreIssueController@downloadDetailsPdf');
    Route::post('/receiveStock', 'MainStoreIssueController@receiveStock');
    Route::post('/receiveBulkStock', 'MainStoreIssueController@receiveBulkStock');
});

Route::prefix('mx_stock_movt')->namespace('Stock')->group(function () {
    Route::get('/fetchStockMovement', 'MixedStockMovtController@fetchStockMovement');
    Route::get('/fetchMovtStoreStatus', 'MixedStockMovtController@fetchMovtStoreStatus');
    Route::POST('/updatePhysicalStock', 'MixedStockMovtController@updatePhysicalStock');
    Route::get('/fetchAll', 'MixedStockMovtController@fetchAll');
});

Route::prefix('online_orders')->namespace('Online')->group(function () {
    Route::get('/fetchOrders', 'OrdersController@fetchOrders');
    Route::post('/updateOrderPrintStatus', 'OrdersController@updateOrderPrintStatus');
    Route::delete('/destroyOrder/{order_no}', 'OrdersController@destroyOrder');
});
Route::prefix('online_sales')->namespace('Sale')->group(function () {
    Route::post('/create', 'OnlineSaleController@create');
});


Route::prefix('kitchen_items')->namespace('Stock')->group(function () {
    Route::post('/create', 'KitchenItemController@create');
    Route::get('/fetch', 'KitchenItemController@fetch');
    Route::delete('/remove/{id}', 'KitchenItemController@remove');
});
Route::prefix('drinks_items')->namespace('Stock')->group(function () {
    Route::post('/create', 'DrinksItemController@create');
    Route::get('/fetch', 'DrinksItemController@fetch');
    Route::delete('/remove/{id}', 'DrinksItemController@remove');
});

Route::prefix('expense_type')->namespace('Expenses')->group(function () {
    Route::post('/create', [ExpenseTypeController::class, 'create']);
    Route::put('/update', [ExpenseTypeController::class, 'update']);
    Route::delete('/delete/{id}', [ExpenseTypeController::class, 'delete']);
    Route::get('/fetch', [ExpenseTypeController::class, 'fetch']);
});
Route::prefix('expense')->namespace('Expenses')->group(function () {
    Route::post('/create', [ExpensesController::class, 'create']);
    Route::delete('/destroy/{id}', [ExpensesController::class, 'destroy']);
    Route::get('/fetch', [ExpensesController::class, 'fetch']);
});
Route::prefix('petty_cash')->namespace('Expenses')->group(function () {
    Route::post('/create', 'PettyCashController@create');
    Route::delete('/destroy/{id}', 'PettyCashController@destroy');
    Route::get('/fetch', 'PettyCashController@fetch');
});
Route::prefix('issue_home')->namespace('Stock')->group(function () {
    Route::post('/create', 'HomeIssueController@create');
    Route::post('/destroy', 'HomeIssueController@destroy');
    Route::get('/fetch', 'HomeIssueController@fetch');
});
Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::post('/create', [InventoryController::class, 'create']);
    Route::post('/destroy', [InventoryController::class, 'destroy']);
    Route::get('/fetch', [InventoryController::class, 'fetch']);
    Route::post('/updateStockQty', [InventoryController::class, 'updateStockQty']);
    Route::post('/addDeductStockQty', [InventoryController::class, 'addDeductStockQty']);
    Route::get('/searchItem', [InventoryController::class, 'searchItem']);
    Route::get('/fetchInventoryValue', [InventoryController::class, 'fetchInventoryValue']);
    Route::post('/emptyInventory', [InventoryController::class, 'emptyInventory']);
    Route::get('/getItemQty', [InventoryController::class, 'getItemQty']);
    Route::get('/fetchMovementHistory', [InventoryController::class, 'fetchMovementHistory']);
    Route::get('/fetchItemHistoryExcel', [InventoryController::class, 'fetchItemHistoryExcel']);
    Route::get('/getLowStock', [InventoryController::class, 'getLowStock']);
    Route::get('/searchItemChunk', [InventoryController::class, 'searchItemChunk']);
    Route::get('/fetchBranchComparisonReport', [InventoryController::class, 'fetchBranchComparisonReport']);
    Route::post('/syncInventoryFromStockList', [InventoryController::class, 'syncInventoryFromStockList']);
    Route::get('/inventoryTotals', [InventoryController::class, 'inventoryTotals']);
    Route::get('/fetchInventoryLogs', [InventoryController::class, 'fetchInventoryLogs']);
});
Route::prefix('pos_credit')->namespace('Credit')->group(function () {
    Route::post('/create', [POSCreditController::class, 'create']);
    Route::get('/fetchPendingCr', [POSCreditController::class, 'fetchPendingCr']);
    Route::delete('/destroy/{id}', [POSCreditController::class, 'destroy']);
    Route::post('/completeCr', [POSCreditController::class, 'completeCr']);
    Route::get('/fetch', [POSCreditController::class, 'fetch']);
    Route::get('/fetchTotal', [POSCreditController::class, 'fetchTotal']);
    Route::delete('/deleteCCompletedCr/{id}', [POSCreditController::class, 'deleteCCompletedCr']);
    Route::post('/exchangeWithOriginalReceipt', [POSCreditController::class, 'exchangeWithOriginalReceipt']);
    Route::post('/commitRefund', [POSCreditController::class, 'commitRefund']);
    Route::get('/fetchRefunds', [POSCreditController::class, 'fetchRefunds']);
    Route::get('/fetchRefundsTotal', [POSCreditController::class, 'fetchRefundsTotal']);
    Route::post('/exchangeWithPartialOriginalReceipt', [POSCreditController::class, 'exchangeWithPartialOriginalReceipt']);
});
Route::prefix('product_mapping')->namespace('Stock')->group(function () {
    Route::post('/create', [ProductMappingController::class, 'create']);
    Route::get('/fetch', [ProductMappingController::class, 'fetch']);
    Route::get('/fetchQty', [ProductMappingController::class, 'fetchQty']);

    Route::delete('/destroy/{id}', [ProductMappingController::class, 'destroy']);
});
Route::prefix('waste_reason')->namespace('Waste')->group(function () {
    Route::post('/create', 'WasteReasonController@create');
    Route::get('/fetch', 'WasteReasonController@fetch');
    Route::put('/update', 'WasteReasonController@update');
    Route::delete('/destroy/{id}', 'WasteReasonController@destroy');
});
Route::prefix('waste_record')->namespace('Waste')->group(function () {
    Route::post('/create', [WasteRecordController::class, 'create']);
    Route::get('/fetch', [WasteRecordController::class, 'fetch']);
    Route::get('/fetchWasteNo', [WasteRecordController::class, 'fetchWasteNo']);
    Route::get('/fetchTotals', [WasteRecordController::class, 'fetchTotals']);
    Route::get('/fetchWasteItem', [WasteRecordController::class, 'fetchWasteItem']);
    Route::post('/complete', [WasteRecordController::class, 'complete']);
    Route::delete('/destroy/{id}', [WasteRecordController::class, 'destroy']);
});
Route::prefix('ledger_account')->namespace('LedgerAccount')->group(function () {
    Route::post('/create', [LedgerAccountController::class, 'create']);
    Route::put('/update', [LedgerAccountController::class, 'update']);
    Route::get('/fetch', [LedgerAccountController::class, 'fetch']);

    Route::delete('/destroy/{id}', [LedgerAccountController::class, 'destroy']);

    Route::get('/fetchExpenseTypes', [LedgerAccountController::class, 'fetchExpenseTypes']);
});
Route::prefix('main_ledger_account')->group(function () {
    Route::post('/create', [MainLedgerAccountController::class, 'create']);
    Route::put('/update/{id}', [MainLedgerAccountController::class, 'update']);
    Route::get('/fetch', [MainLedgerAccountController::class, 'fetch']);

    Route::delete('/destroy/{id}', [MainLedgerAccountController::class, 'destroy']);
});
Route::prefix('general_ledger')->namespace('GeneralLedger')->group(function () {
    Route::post('/create', [GeneralLedgerController::class, 'create']);

    Route::get('/fetchEntryNo', [GeneralLedgerController::class, 'fetchEntryNo']);

    Route::get('/fetchPending', [GeneralLedgerController::class, 'fetchPending']);

    Route::delete('/destroy/{id}', [GeneralLedgerController::class, 'destroy']);
    Route::post('/completeLedger', [GeneralLedgerController::class, 'completeLedger']);
    Route::get('/fetchLedgerGrouped', [GeneralLedgerController::class, 'fetchLedgerGrouped']);
    Route::get('/fetchLedgerTotal', [GeneralLedgerController::class, 'fetchLedgerTotal']);
    Route::get('/fetchDetailsEntries', [GeneralLedgerController::class, 'fetchDetailsEntries']);
    Route::post('/saveBalances', [GeneralLedgerController::class, 'saveBalances']);
    Route::get('/fetchVatReport', [GeneralLedgerController::class, 'fetchVatReport']);
});

Route::prefix('general_ledger')->namespace('GeneralLedger')->group(function () {
    Route::get('/generalLedgerStatement', [GeneralLedgerReportController::class, 'generalLedgerStatement']);
    Route::get('/fetchLedgerStatement', [GeneralLedgerReportController::class, 'fetchLedgerStatement']);
    Route::delete('/deleteStatement/{id}', [GeneralLedgerReportController::class, 'deleteStatement']);
    Route::get('/downloadStatment', [GeneralLedgerReportController::class, 'downloadStatment']);
    Route::get('/fetchAll', [GeneralLedgerReportController::class, 'fetchAll']);
});
Route::prefix('chart_accounts')->namespace('ChartAccount')->group(function () {
    Route::post('/create', [ChartAccountController::class, 'create']);
    Route::put('/update', [ChartAccountController::class, 'update']);

    Route::delete('/destroy/{id}', [ChartAccountController::class, 'destroy']);
    Route::get('/fetch', [ChartAccountController::class, 'fetch']);
});

Route::prefix('trial_balance')->namespace('GeneralLedger')->group(function () {
    Route::post('/create', [TrialBalanceController::class, 'create']);
    Route::put('/update', [TrialBalanceController::class, 'update']);

    Route::delete('/destroy/{id}', [TrialBalanceController::class, 'destroy']);
    Route::get('/fetch', [TrialBalanceController::class, 'fetch']);
    Route::get('/fetchAssets', [TrialBalanceController::class, 'fetchAssets']);
    Route::get('/fetchLiabilities', [TrialBalanceController::class, 'fetchLiabilities']);
    Route::get('/taxAmount', 'TrialBalanceController@taxAmount');
    Route::get('/fetchExpenses', 'TrialBalanceController@fetchExpenses');
});
Route::prefix('expense_payment')->namespace('Expenses')->group(function () {
    Route::post('/create', 'ExpensePaymentController@create');
    Route::get('/fetchPaymentReport', 'ExpensePaymentController@fetchPaymentReport');
    Route::get('/fetch', 'ExpensePaymentController@fetch');
    Route::get('/fetchPaymentTotal', 'ExpensePaymentController@fetchPaymentTotal');
    Route::post('/getAmountOwed', 'ExpensePaymentController@getAmountOwed');

    Route::delete('/destroy/{id}', 'ExpensePaymentController@destroy');
});
Route::prefix('supplier_creditnote')->namespace('SupplierCreditNote')->group(function () {
    Route::post('/create', 'SupplierCreditNoteController@create');

    Route::get('/fetch', 'SupplierCreditNoteController@fetch');
    Route::get('/fetchTotal', 'SupplierCreditNoteController@fetchTotal');

    Route::delete('/destroy/{id}', 'SupplierCreditNoteController@destroy');
});
Route::prefix('assets_category')->namespace('Assets')->group(function () {
    Route::post('/create', 'AssetCategoryController@create');
    Route::put('/update', 'AssetCategoryController@update');

    Route::get('/fetch', 'AssetCategoryController@fetch');


    Route::delete('/destroy/{id}', 'AssetCategoryController@destroy');
});

Route::prefix('assets')->namespace('Assets')->group(function () {
    Route::post('/create', 'AssetsController@create');
    Route::put('/update', 'AssetsController@update');

    Route::get('/fetch', 'AssetsController@fetch');
    Route::get('/fetchTotal', 'AssetsController@fetchTotal');


    Route::delete('/destroy/{id}', 'AssetsController@destroy');
});

Route::prefix('asset_allocation')->namespace('Assets')->group(function () {
    Route::post('/create', 'AssetAllocationController@create');
    Route::put('/update', 'AssetAllocationController@update');

    Route::get('/fetch', 'AssetAllocationController@fetch');
    Route::get('/fetchTotal', 'AssetAllocationController@fetchTotal');


    Route::delete('/destroy/{id}', 'AssetAllocationController@destroy');
    Route::delete('/revoke/{id}', 'AssetAllocationController@revoke');
    Route::delete('/reAllocate/{id}', 'AssetAllocationController@reAllocate');
});

Route::prefix('asset_maintenance')->namespace('Assets')->group(function () {
    Route::post('/create', 'AssetMaintenanceController@create');
    Route::put('/updateStatus', 'AssetMaintenanceController@updateStatus');

    Route::get('/fetch', 'AssetMaintenanceController@fetch');


    Route::delete('/destroy/{id}', 'AssetMaintenanceController@destroy');
});
Route::prefix('profit_loss')->namespace('GeneralLedger')->group(function () {

    Route::get('/fetchAssets', [ProfitLossController::class, 'fetchAssets']);
    Route::get('/fetchLiabilities', [ProfitLossController::class, 'fetchLiabilities']);
    Route::get('/taxAmount', [ProfitLossController::class, 'taxAmount']);
    Route::get('/totalPayroll', [ProfitLossController::class, 'totalPayroll']);
    Route::get('/totalSales', [ProfitLossController::class, 'totalSales']);
    Route::get('/totalcostOfGoodSold', [ProfitLossController::class, 'totalcostOfGoodSold']);
    Route::get('/profitLossAmount', [ProfitLossController::class, 'profitLossAmount']);
    Route::get('/incomeFromInvoicePayments', [ProfitLossController::class, 'incomeFromInvoicePayments']);
    Route::get('/profitOrLossAmount', [ProfitLossController::class, 'profitOrLossAmount']);
    Route::get('/fetchProfitLoss', [ProfitLossController::class, 'fetchProfitLoss']);
});
Route::prefix('leave_type')->namespace('Employee')->group(function () {


    Route::get('/fetch', 'LeaveTypeController@fetch');
    Route::post('/create', 'LeaveTypeController@create');
    Route::delete('/destroy/{id}', 'LeaveTypeController@destroy');
});

Route::prefix('leave')->namespace('Leave')->group(function () {


    Route::get('/fetch', 'LeaveController@fetch');
    Route::post('/create', 'LeaveController@create');
    Route::delete('/destroy/{id}', 'LeaveController@destroy');
});

Route::prefix('dashboard')->namespace('GeneralLedger')->group(function () {


    Route::get('/fetch', 'DashboardReportController@fetch');
});

Route::prefix('quote')->namespace('Quote')->group(function () {

    Route::get('/fetch', 'QuoteController@fetch');
    Route::post('/orders', 'QuoteController@orders');
    Route::post('/create', 'QuoteController@create');
    Route::get('/orderNumber/{id}', 'QuoteController@orderNumber');
    Route::post('/cancelOrders', 'QuoteController@cancelOrders');
    Route::post('/completeOrder', 'QuoteController@completeOrder');
    Route::get('/generateQuote/{order_no}', 'QuoteController@generateQuote');
    Route::post('/fetchCompleteQuoteByQuoteNo', 'QuoteController@fetchCompleteQuoteByQuoteNo');
    Route::get('/getPrintData', 'QuoteController@getPrintData');
    Route::post('/destroy', 'QuoteController@destroy');
});
Route::prefix('price_groups')->namespace('PriceGroups')->group(function () {
    Route::get('/fetch', 'PriceGroupController@fetch');
    Route::post('/create', 'PriceGroupController@create');
    Route::put('/update', 'PriceGroupController@update');
    Route::delete('/destroy/{id}', 'PriceGroupController@destroy');
});

Route::prefix('price_group_amount')->namespace('PriceGroups')->group(function () {
    Route::get('/fetch', 'PriceGroupAmountController@fetch');
    Route::post('/create', 'PriceGroupAmountController@create');
    Route::put('/update', 'PriceGroupAmountController@update');
    Route::delete('/destroy/{id}', 'PriceGroupAmountController@destroy');
    Route::post('/uploadPriceGroup', 'PriceGroupAmountController@uploadPriceGroup');
    Route::get('/fetchExcelSample', 'PriceGroupAmountController@fetchExcelSample');
});

Route::prefix('balance_sheet')->namespace('GeneralLedger')->group(function () {
    Route::get('/fetchFixedAsset', [BalanceSheetController::class, 'fetchFixedAsset']);
    Route::get('/fetchCurrentAsset', [BalanceSheetController::class, 'fetchCurrentAsset']);
    Route::get('/fetchOtherCurrentAsset', [BalanceSheetController::class, 'fetchOtherCurrentAsset']);


    Route::get('/fetchCurrentLiabilities', [BalanceSheetController::class, 'fetchCurrentLiabilities']);
    Route::get('/fetchLongTermLiabilities', [BalanceSheetController::class, 'fetchLongTermLiabilities']);
    Route::get('/accountPayable', [BalanceSheetController::class, 'accountPayable']);
    Route::get('/fetchEquity', [BalanceSheetController::class, 'fetchEquity']);
    Route::get('/fetchExpenses', [BalanceSheetController::class, 'fetchExpenses']);
    Route::get('/summary', [BalanceSheetController::class, 'summary']);
});


Route::prefix('issue_conversion')->namespace('Stock')->group(function () {
    Route::post('/create', 'StockConversionIssueController@create');
    Route::get('/fetch', 'StockConversionIssueController@fetch');
    Route::get('/fetchIssueNo', 'StockConversionIssueController@fetchIssueNo');
    Route::post('/destroy', 'StockConversionIssueController@destroy');
    Route::post('/completeIssue', 'StockConversionIssueController@completeIssue');
});
Route::prefix('direct_credit_note')->namespace('CreditNote')->group(function () {
    Route::post('/create', 'DirectCreditNoteController@create');
    Route::post('/complete', 'DirectCreditNoteController@complete');
    Route::get('/fetch', 'DirectCreditNoteController@fetch');
    Route::get('/fetchPendingCr', 'DirectCreditNoteController@fetchPendingCr');
    Route::delete('/destroy/{id}', 'DirectCreditNoteController@destroy');
    Route::get('/fetchDetails', 'DirectCreditNoteController@fetchDetails');
});

Route::prefix('digitax_customer')->namespace('Digitax')->group(function () {
    Route::post('/sendApiRequest', 'CustomerController@sendApiRequest');
    Route::post('/addCustomerEtims', 'CustomerController@addCustomerEtims');
});
Route::prefix('digitax_stock')->namespace('Digitax')->group(function () {
    Route::post('/addItems', [EtimsStockController::class, 'addItems']);
    Route::put('/updateItems', [EtimsStockController::class, 'updateItems']);
    Route::put('/updateStockQtyEtims', [EtimsStockController::class, 'updateStockQtyEtims']);
    Route::get('/bulkInventoyEtimsSync', [EtimsStockController::class, 'bulkInventoyEtimsSync']);
    Route::get('/bulkInventoyEtimsSyncZeroQty', [EtimsStockController::class, 'bulkInventoyEtimsSyncZeroQty']);
    Route::get('/bulkInventoyEtimsSyncZeroQty', [EtimsStockController::class, 'bulkInventoyEtimsSyncZeroQty']);

    Route::post('/bulkAddItems', [EtimsStockController::class, 'bulkAddItems']);
});



Route::prefix('shelves')->namespace('Shelf')->group(function () {
    Route::post('/create', [ShelfController::class, 'create']);
    Route::get('/fetch', [ShelfController::class, 'fetch']);
    Route::put('/update', [ShelfController::class, 'update']);
    Route::delete('/destroy/{id}', [ShelfController::class, 'destroy']);
    Route::post('/allocate', [ShelfController::class, 'allocate']);
    Route::get('/fetchAllocation', [ShelfController::class, 'fetchAllocation']);
    Route::delete('/destroyAllocation/{id}', [ShelfController::class, 'destroyAllocation']);
    Route::post('/importShelf', [ShelfController::class, 'importShelf']);
});

Route::prefix('card_terminal')->namespace('Card')->group(function () {
    Route::post('/create', 'CardTerminalController@create');
    Route::get('/fetch', 'CardTerminalController@fetch');
    Route::put('/update', 'CardTerminalController@update');
    Route::delete('/destroy/{id}', 'CardTerminalController@destroy');
});
Route::prefix('etims_item_class_code')->namespace('Etims')->group(function () {
    Route::post('/create', 'EtimsItemClassificationCodeController@create');
    Route::get('/fetch', 'EtimsItemClassificationCodeController@fetch');
    Route::put('/update', 'EtimsItemClassificationCodeController@update');
    Route::delete('/destroy/{id}', 'EtimsItemClassificationCodeController@destroy');
});
Route::prefix('etims_item_type_code')->namespace('Etims')->group(function () {
    Route::post('/create', 'EtimsItemTypeController@create');
    Route::get('/fetch', 'EtimsItemTypeController@fetch');
    Route::put('/update', 'EtimsItemTypeController@update');
    Route::delete('/destroy/{id}', 'EtimsItemTypeController@destroy');
});
Route::prefix('etims_country_origin_code')->namespace('Etims')->group(function () {
    Route::post('/create', 'EtimsCountryOriginController@create');
    Route::get('/fetch', 'EtimsCountryOriginController@fetch');
    Route::put('/update', 'EtimsCountryOriginController@update');
    Route::delete('/destroy/{id}', 'EtimsCountryOriginController@destroy');
});
Route::prefix('etims_item_packaging_code')->namespace('Etims')->group(function () {
    Route::post('/create', 'EtimsItemPackagingCodeController@create');
    Route::get('/fetch', 'EtimsItemPackagingCodeController@fetch');
    Route::put('/update', 'EtimsItemPackagingCodeController@update');
    Route::delete('/destroy/{id}', 'EtimsItemPackagingCodeController@destroy');
});
Route::prefix('etims_report')->namespace('Etims')->group(function () {
    Route::post('/create', 'EtimsSalesReportController@create');
    Route::get('/fetchDirectFromEtims', 'EtimsSalesReportController@fetchDirectFromEtims');
    Route::put('/update', 'EtimsSalesReportController@update');
    Route::delete('/destroy/{id}', 'EtimsSalesReportController@destroy');
});


Route::prefix('stock_history')->namespace('Inventory')->group(function () {
    Route::get('/getStockMovementHistory', 'StockHistoryController@getStockMovementHistory');
});

Route::prefix('stock_stake')->namespace('Inventory')->group(function () {
    Route::post('/create', 'BulkInventoryController@create');
    Route::get('/fetchGrouped', 'BulkInventoryController@fetchGrouped');

    Route::get('/fetchDetails', 'BulkInventoryController@fetchDetails');
    Route::post('/importCSV', 'BulkInventoryController@importCSV');
});

Route::prefix('temp_po')->namespace('PurchaseOrder')->group(function () {
    Route::post('/create', 'TempPurchaseOrderController@create');
    Route::post('/completeOrder', 'TempPurchaseOrderController@completeOrder');
    Route::get('/fetch', 'TempPurchaseOrderController@fetch');
    Route::delete('/destroy/{id}', 'TempPurchaseOrderController@destroy');
});

Route::prefix('grn_returns')->namespace('Returns')->group(function () {
    Route::post('/create', [GRNReturnController::class, 'create']);
    Route::get('/fetchGrouped', [GRNReturnController::class, 'fetchGrouped']);
    Route::get('/fetchReturnDetails', [GRNReturnController::class, 'fetchReturnDetails']);
    Route::get('/fetchTotal', [GRNReturnController::class, 'fetchTotal']);
    Route::post('/downloadDN', [GRNReturnController::class, 'downloadDN']);

    Route::get('/fetchItemized', 'GRNReturnController@fetchItemized');
    Route::delete('/destroy/{id}', 'GRNReturnController@destroy');
});
Route::prefix('medical')->namespace('Medical')->group(function () {
    Route::post('/create', 'MedicalRecordController@create');
    Route::post('/update', 'MedicalRecordController@update');
    Route::post('/addPrescription', 'MedicalRecordController@addPrescription');
    Route::put('/updatePrescription', 'MedicalRecordController@updatePrescription');
    Route::get('/fetch', 'MedicalRecordController@fetch');
    Route::get('/fetchPendingDescription', 'MedicalRecordController@fetchPendingDescription');
    Route::get('/fetchMedicalHistory', 'MedicalRecordController@fetchMedicalHistory');
    Route::get('/fetchPrescription', 'MedicalRecordController@fetchPrescription');

    Route::delete('/destroy/{id}', 'MedicalRecordController@destroy');
    Route::post('/deleteMedicalRecord', 'MedicalRecordController@deleteMedicalRecord');
    Route::get('/fetchPatient', 'MedicalRecordController@fetchPatient');
    Route::get('/fetchPatientMedicalHistory', 'MedicalRecordController@fetchPatientMedicalHistory');
});

Route::prefix('sickoff')->namespace('Sickoff')->group(function () {
    Route::post('/create', 'SickoffController@create');
    Route::get('/fetch', 'SickoffController@fetch');
    Route::delete('/destroy/{id}', 'SickoffController@destroy');
});
Route::prefix('lab')->namespace('Lab')->group(function () {
    Route::post('/create', 'LabController@create');
    Route::post('/update', 'LabController@update');
    Route::get('/fetch', 'LabController@fetch');
    Route::get('/fetchLabProduct', 'LabController@fetchLabProduct');
    Route::get('/fetchPendingProducts', 'LabController@fetchPendingProducts');
    Route::delete('/destroy/{id}', 'LabController@destroy');
    Route::post('/addLabProducts', 'LabController@addLabProducts');
    Route::put('/updateLabProducts', 'LabController@updateLabProducts');
    Route::get('/fetchLabHistory', 'LabController@fetchLabHistory');
    Route::post('/deleteLabRecord', 'LabController@deleteLabRecord');
    Route::get('/fetchLabResultByEntryCode', 'LabController@fetchLabResultByEntryCode');
    Route::get('/fetchCompletedLabresult', 'LabController@fetchCompletedLabresult');
});

Route::prefix('branch_stock_transfer')->namespace('BranchStockTransfer')->group(function () {
    Route::post('/create', [BranchStockTransferController::class, 'create']);
    Route::get('/fetch', [BranchStockTransferController::class, 'fetch']);
    Route::get('/fetchPending', [BranchStockTransferController::class, 'fetchPending']);
    Route::post('/completeTransfer', [BranchStockTransferController::class, 'completeTransfer']);

    Route::delete('/destroy/{id}', [BranchStockTransferController::class, 'destroy']);
    Route::get('/fetchDetails', [BranchStockTransferController::class, 'fetchDetails']);
    Route::get('/fetchAll', [BranchStockTransferController::class, 'fetchAll']);
    Route::get('/fetchUnreceivedTransfer', [BranchStockTransferController::class, 'fetchUnreceivedTransfer']);
    Route::get('/fetchReceivable', [BranchStockTransferController::class, 'fetchReceivable']);
    Route::get('/fetchUnreceivedGroupedTransfer', [BranchStockTransferController::class, 'fetchUnreceivedGroupedTransfer']);


    Route::post('/receiveTransfer', [BranchStockTransferController::class, 'receiveTransfer']);
    Route::post('/receiveAll', [BranchStockTransferController::class, 'receiveAll']);
    Route::get('/fetchReceived', [BranchStockTransferController::class, 'fetchReceived']);
    Route::post('/destroy', [BranchStockTransferController::class, 'destroy']);
    Route::post('/deleteTransfer', [BranchStockTransferController::class, 'deleteTransfer']);
            Route::get('/viewDetails', [BranchStockTransferController::class, 'viewDetails']);

    Route::get('/fetchLostTransfer', [BranchStockTransferController::class, 'fetchLostTransfer']);
});
Route::prefix('pgm_stock')->namespace('PgmEtims')->group(function () {
    Route::post('/addItems', 'EtimStockController@addItems');
    Route::put('/updateItems', 'EtimStockController@updateItems');
    Route::post('/stockMovt', 'EtimStockController@stockMovt');
    Route::post('/syncwithEtims', 'EtimStockController@syncwithEtims');
    Route::get('/syncAllStockToEtims', 'EtimStockController@syncAllStockToEtims');
});
Route::prefix('register')->namespace('Register')->group(function () {
    Route::post('/create', 'RegisterController@create');
    Route::post('/closeRegister', [RegisterController::class, 'closeRegister']);

    Route::get('/fetchRegisterOpeningBalances', 'RegisterController@fetchRegisterOpeningBalances');
    Route::delete('/destroy/{id}', 'RegisterController@destroy');
    Route::get('/fetch', 'RegisterController@fetch');
    Route::get('/fetchCashierOnShift', 'RegisterController@fetchCashierOnShift');
    Route::post('/fetchBalancesDetails', 'RegisterController@fetchBalancesDetails');
    Route::post('/fetchBalancesDetailsItemized', 'RegisterController@fetchBalancesDetailsItemized');
    Route::get('/fetchClosingShift', [RegisterController::class, 'fetchClosingShift']);
});
Route::prefix('cashdrops')->namespace('Cashdrops')->group(function () {
    Route::post('/create', 'CashdropController@create');
    Route::get('/fetch', 'CashdropController@fetch');
    Route::put('/update', 'CashdropController@update');
    Route::delete('/destroy/{ref_no}', 'CashdropController@destroy');
    Route::get('/getAccountById', 'CashdropController@getAccountById');
});

Route::prefix('mpesadrops')->namespace('MpesaDrop')->group(function () {
    Route::post('/create', 'MpesaDropController@create');
    Route::get('/fetch', 'MpesaDropController@fetch');
    Route::put('/update', 'MpesaDropController@update');
    Route::delete('/destroy/{id}', 'MpesaDropController@destroy');
    Route::get('/getAccountById', 'MpesaDropController@getAccountById');
});


Route::prefix('merge_stock')->namespace('Stock')->group(function () {

    Route::post('/create', 'MergeStockController@create');


    Route::get('/fetch', 'MergeStockController@fetch');
});
Route::prefix('preorders')->namespace('PreOrders')->group(function () {

    Route::post('/create', 'PreorderControllers@create');


    Route::get('/fetch', 'PreorderControllers@fetch');
    Route::get('/fetchProgress', 'PreorderControllers@fetchProgress');
    Route::delete('/destroy/{id}', 'PreorderControllers@destroy');
    Route::get('/fetchGroupedPreorder', 'PreorderControllers@fetchGroupedPreorder');
    Route::delete('/deleteOrder/{order_no}', 'PreorderControllers@deleteOrder');
    Route::get('/getByOrderNo', 'PreorderControllers@getByOrderNo');
    Route::post('/completeOrder', 'PreorderControllers@completeOrder');
    Route::post('/completeDelivery', 'PreorderControllers@completeDelivery');
});



Route::prefix('directretail_creditnote')->namespace('DirectRetailsCredit')->group(function () {

    Route::post('/create', 'DirectRetailsCreditController@create');


    Route::get('/fetch', 'DirectRetailsCreditController@fetch');
    Route::get('/fetchProgress', 'DirectRetailsCreditController@fetchProgress');
    Route::get('/fetchReplacementProgress', 'DirectRetailsCreditController@fetchReplacementProgress');
    Route::delete('/destroy/{id}', 'DirectRetailsCreditController@destroy');

    Route::delete('/deleteOrder/{order_no}', 'DirectRetailsCreditController@deleteOrder');
    Route::get('/groupByCreditNo', 'DirectRetailsCreditController@groupByCreditNo');
    Route::get('/getByCreditNo', 'DirectRetailsCreditController@getByCreditNo');
    Route::get('/fetchCreditByCreditNo', 'DirectRetailsCreditController@fetchCreditByCreditNo');
    Route::post('/completeCreditNote', 'DirectRetailsCreditController@completeCreditNote');
    Route::post('/createReplacement', 'DirectRetailsCreditController@createReplacement');
    Route::get('/fetchReplacement', 'DirectRetailsCreditController@fetchReplacement');
    Route::post('/completeExchangeMatch', 'DirectRetailsCreditController@completeExchangeMatch');
    Route::post('/deleteCreditNote', 'DirectRetailsCreditController@deleteCreditNote');
});


Route::prefix('label_printer')->namespace('PrintLabels')->group(function () {

    Route::post('/printLabels', 'PrintLabelController@printLabels');
});
Route::prefix('label_dimension')->namespace('PrintLabels')->group(function () {
    Route::post('/create', 'PrintLabelDimensionController@create');
    Route::put('/update', 'PrintLabelDimensionController@update');
    Route::get('/fetch', 'PrintLabelDimensionController@fetch');
    Route::delete('/deleteDimension/{id}', 'PrintLabelDimensionController@deleteDimension');
});
Route::prefix('promos')->namespace('Promos')->group(function () {
    Route::post('/create', 'PromosController@create');
    Route::put('/update', 'PromosController@update');
    Route::get('/fetch', 'PromosController@fetch');
    Route::delete('/destroy/{id}', 'PromosController@destroy');
});
Route::prefix('branch_accounts')->group(function () {
    Route::post('create', [BranchAccountController::class, 'create']);
    Route::get('fetch', [BranchAccountController::class, 'fetch']);
    Route::get('fetchBranchAccounts', [BranchAccountController::class, 'fetchBranchAccounts']);
    Route::delete('/destroy/{id}',  [BranchAccountController::class, 'destroy']);
});


Route::prefix('model_logs')->group(function () {
    Route::get('fetch', [ModelLogController::class, 'fetch']);
});
Route::prefix('closing_stock')->group(function () {
    Route::get('fetch', [StockHistoryInventoryController::class, 'fetch']);
});


Route::prefix('qb_bank_reconciliation')->group(function () {

    Route::get('/fetchPreviousReconsClosing', [QBBankReconciliationController::class, 'fetchPreviousReconsClosing']);
    Route::get('/fetchUnreconciledStatements', [QBBankReconciliationController::class, 'fetchUnreconciledStatements']);
    Route::post('/create', [QBBankReconciliationController::class, 'create']);
    Route::get('/fetchPendingStatements', [QBBankReconciliationController::class, 'fetchPendingStatements']);
    Route::post('/completeRecons', [QBBankReconciliationController::class, 'completeRecons']);
    Route::get('/fetchReconByRef', [QBBankReconciliationController::class, 'fetchReconByRef']);
    Route::get('/fetchReconsDetailedReport', [QBBankReconciliationController::class, 'fetchReconsDetailedReport']);
    Route::get('/fetchUnclearedReconsDetailedReport', [QBBankReconciliationController::class, 'fetchUnclearedReconsDetailedReport']);
    Route::post('/saveBankStatement', [QBBankReconciliationController::class, 'saveBankStatement']);
    Route::get('/fetchBankStatements', [QBBankReconciliationController::class, 'fetchBankStatements']);
    Route::get('/fetchMatchingTransaction', [QBBankReconciliationController::class, 'fetchMatchingTransaction']);
    Route::post('/reconcileMatch', [QBBankReconciliationController::class, 'reconcileMatch']);
    Route::post('/importBankStatement', [QBBankReconciliationController::class, 'importBankStatement']);
    Route::delete('/deleteStatement/{id}', [QBBankReconciliationController::class, 'deleteStatement']);
    Route::get('/fetchByAccountID', [QBBankReconciliationController::class, 'fetchByAccountID']);

});