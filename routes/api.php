<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/fetchIssuedStock','EndPoint\IssueSyncController@fetchIssuedStock');

Route::post('/updateIssuedStockLocally','EndPoint\IssueSyncController@updateIssuedStockLocally');

Route::post('/updateIssueCompletedStatus','EndPoint\IssueSyncController@updateIssueCompletedStatus');


Route::get('/fetchNewStock','EndPoint\StockSyncController@fetchNewStock');
Route::post('/updateNewStockLocally','EndPoint\StockSyncController@updateNewStockLocally');
Route::post('/updateStockSyncStatus','EndPoint\StockSyncController@updateStockSyncStatus');


Route::get('/fetchNewCategory','EndPoint\StockSyncController@fetchNewCategory');
Route::post('/updateNewCategoriesLocally','EndPoint\StockSyncController@updateNewCategoriesLocally');
Route::post('/updateCategorySyncStatus','EndPoint\StockSyncController@updateCategorySyncStatus');


Route::get('/fetchNewUnit','EndPoint\StockSyncController@fetchNewUnit');
Route::post('/updateNewUnitLocally','EndPoint\StockSyncController@updateNewUnitLocally');
Route::post('/updateUnitSyncStatus','EndPoint\StockSyncController@updateUnitSyncStatus');
Route::post('/externalLogin', 'AdminController@externalLogin');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/testPrint', 'Sale\POSSaleController@testPrint');
