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

//app routes
Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => 'sf/v1', 'middleware' => ['api','custom_auth']], function () {
    Route::get('/user_profile', 'UserController@get_profile');
    Route::post('/user_profile', 'UserController@create_update_profile');
    Route::post('/add_address', 'UserController@add_new_address');
    Route::put('/edit_address/{id}', 'UserController@update_address');
    
});

//auth routes
Route::group(['namespace' => 'App\Http\Controllers\Api', 'prefix' => '', 'middleware' => ['api']], function () {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/logout', 'AuthController@logout')->middleware('custom_auth');
    Route::get('/getuser', 'AuthController@user')->middleware('custom_auth');  
});