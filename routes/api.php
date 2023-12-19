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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'App\Http\Controllers', 'prefix' => 'sf/v1', 'middleware' => ['api']], function () {
    Route::get('/', 'BidWarsController@index');
});


Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => '', 'middleware' => ['api']], function () {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/logout', 'AuthController@logout')->middleware('custom_auth');
    Route::get('/user', 'AuthController@user')->middleware('custom_auth');
});