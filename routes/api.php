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
});
