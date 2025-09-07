<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Route::get('/', 'AdminController@home');
Route::get('/', function () {
    return view('welcome');

    return view('online_orders.home');
});
Route::get('/session_monitor', 'AdminController@sessionMonitor');
Route::get('/clear_cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return "cache cleared";
});
Route::get('/run_migrations', function () {
    return Artisan::call('migrate', ["--force" => true]);
});
Route::get('/login_pos', function () {
    return view('pos');
});
Route::get('/login', function () {
    return view('welcome');
});


Route::get('landing_page', function () {
    return view('landing_page');
});
Route::get('login_external', function () {
    return view('login_external');
});
Route::get('online_ordering', function () {
    return view('online_orders');
});
Route::get('checkout', function () {
    return view('checkout');
});
Route::get('online_booking', function () {
    return view('online');
});
Route::post('/admin_login', 'AdminController@adminLogin');
Route::post('/user_login', 'AdminController@userLogin');
Route::get('/get_username', 'AdminController@getUserName');
Route::get('/logout', 'AdminController@logout');
Route::post('/logoutPOS', 'AdminController@logoutPOS');
Route::post('/externalLoginID', 'AdminController@externalLoginID');
//Route::get('/poslogin', 'Users\POSController@index');
//Route::post('/waiter_login', 'Users\POSController@waiterLogin');
Route::get('/waiter_logout', 'Users\POSController@logout');
Route::prefix('online_menu')->namespace('Online')->group(function () {
    Route::get('/fetch', 'OnlineMenuController@fetch');
    Route::get('/fetchStock', 'OnlineMenuController@fetchStock');
});
Route::prefix('orders')->namespace('Online')->group(function () {
    Route::post('/completeOrder', 'OrdersController@completeOrder');
});
Route::prefix('table_booking')->namespace('TableBooking')->group(function () {
    Route::post('/create', 'TableBookingController@create');
    Route::get('/fetch', 'TableBookingController@fetch');
});
Route::prefix('enquiries')->namespace('Enquiries')->group(function () {
    Route::post('/create', 'EnquiryController@create');
    Route::get('/fetch', 'EnquiryController@fetch');
});
Route::get('/fetchBranches', 'Branch\BranchController@fetch');
//Route::get('/dashboard', 'Dashboard\MainController@index')->middleware(['auth'])->name('dashboard');
Route::prefix('data')->middleware(['web', 'auth'])->group(base_path('routes/web/data.php'));
Route::any('{slug}', 'AdminController@home')->where('slug', '([A-z\d\-\/_.]+)');

Route::get('/{vue_capture?}', function () {
    Route::get('/dashboard', 'AdminController@index')->name('dashboard');
})->where('vue_capture', '[\/\w\.-]*');
