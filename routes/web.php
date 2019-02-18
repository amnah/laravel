<?php

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

Route::get('login', 'AuthController@showLoginForm');
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::get('register', 'AuthController@showRegistrationForm');
Route::post('register', 'AuthController@register');
Route::get('confirm/{email}/{confirmation}', 'AuthController@confirm');
Route::get('forgot', 'AuthController@showLinkRequestForm');
Route::post('forgot', 'AuthController@sendResetLinkEmail');
Route::get('reset/{token}', 'AuthController@showResetForm');
Route::post('reset/{token}', 'AuthController@reset');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/account', 'AppController@getAccount');
});
