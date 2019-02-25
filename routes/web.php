<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'AppController@getIndex');

Route::get('auth/login', 'AuthController@getLogin');
Route::post('auth/login', 'AuthController@postLogin');
Route::post('auth/logout', 'AuthController@postLogout');
Route::get('auth/register', 'AuthController@getRegister');
Route::post('auth/register', 'AuthController@postRegister');
Route::get('auth/confirm/{email}/{confirmation}', 'AuthController@getConfirm');
Route::get('auth/forgot', 'AuthController@getForgot');
Route::post('auth/forgot', 'AuthController@postForgot');
Route::get('auth/reset/{token}', 'AuthController@getReset');
Route::post('auth/reset/{token}', 'AuthController@postReset');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/account', 'AppController@getAccount');
});
