<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::auth();
    Route::get('auth/provider/{provider}', 'Auth\AuthController@redirectToProvider');
    Route::get('auth/provider/callback/{provider}', 'Auth\AuthController@handleProviderCallback');

    Route::get('/home', 'HomeController@index');
});

Route::group(['middleware' => 'cors', 'prefix' => 'api/v1', 'namespace' => 'Api\Auth'], function () {
    //Route::auth();
    //Route::get('login', 'AuthController@showLoginForm');
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('password/email', 'PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'PasswordController@reset');
    //Route::get('password/reset/{token?}', 'PasswordController@showResetForm');
    //Route::get('register', 'AuthController@showRegistrationForm');
    Route::post('register', 'AuthController@register');
});

Route::group(['middleware' => 'cors', 'prefix' => 'api/v1', 'namespace' => 'Api'], function () {
    Route::get('/home', 'HomeController@index');
});