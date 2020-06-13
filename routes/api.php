<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('registered', 'API\ApiUsersControllers@register');
Route::post('login', 'API\ApiUsersControllers@login');
Route::get('get-all-jam-masuk', 'API\AbsensiController@present_test_api')->middleware('jwt.verify');
Route::get('get-user', 'API\AbsensiController@present_test_apiAuth')->middleware('jwt.verify');
Route::get('auth/authenticated', 'API\ApiUsersControllers@getAuthenticatedUser')->middleware('jwt.verify');