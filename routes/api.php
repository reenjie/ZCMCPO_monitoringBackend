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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::namespace('App\Http\Controllers')->group(function () {
    Route::post('login', 'UserController@signIn');   //Login
    Route::post('User', 'UserController@fetchuser'); //FetchUser Data
    Route::post('Roles', 'RoleController@index');
    Route::post('User/New', 'UserController@store');
    Route::post('User/data', 'UserController@index');
    Route::post('User/delete', 'UserController@destroy');
    Route::post('User/update', 'UserController@update');
    Route::post('User/changepass', 'UserController@changepass');
    Route::post('User/changename', 'UserController@changename');
    Route::post('FetchPurchaseOrder', 'PurchaseOrderRequest@index');
    Route::post('FetchAdvanceSortSCU', 'PurchaseOrderRequest@fetchsort');
    Route::post('SetNewtoViewed', 'PurchaseOrderRequest@setviewed');
    Route::post('FetchRecent', 'TransactionController@FetchRecent');
    Route::post('FetchPOstatus', 'TransactionController@FetchPOstatus');
    Route::post('SetStatus', 'TransactionController@SetStatus');
    Route::post('setEmaileddate', 'TransactionController@setEmailedDate');
    Route::post('UndoAction', 'TransactionController@UndoAction');
    Route::post('UpdateDue', 'TransactionController@UpdateDue');
    Route::post('SetDeliveredDate', 'TransactionController@SetDeliveredDate');
    Route::post('Applytoall', 'TransactionController@Applytoall');
    Route::post('MarkComplete', 'TransactionController@MarkComplete');
    Route::post('cardCount', 'TransactionController@cardCount');
    Route::post('filterRecent', 'TransactionController@filterRecent');
    Route::post('fetchlogs', 'UserController@fetchlogs');
    Route::post('logoutUser', 'UserController@logout');
});
