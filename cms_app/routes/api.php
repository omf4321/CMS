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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'ClientAuth\LoginController@api_login');

Route::get('/schedule/{student_id}/{update_time}', 'ApiClientController@get_schedule');
Route::post('/update_profile/{student_id}', 'ApiClientController@update_profile');
Route::post('/change_password/{student_id}', 'ApiClientController@update_password');
Route::get('/take_attendance/{student_id}', 'ApiClientController@take_attendance');
Route::get('/performance/{student_id}', 'ApiClientController@get_performance');
