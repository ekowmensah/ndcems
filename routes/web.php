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

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' =>config('config.SuperAdminUrlPrefix'),'namespace'=>'SuperAdmin','as' => 'SuperAdmin.'], function () {

    // Authentication Routes...
    Route::get('/', function () {
        return redirect(route("SuperAdmin.login"));
    });
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');


    // Password Reset Routes...
    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::group(['middleware' => ['auth:superAdmin']], function () {
        Route::get('/','HomeController@index')->name('home');
        Route::get('/user-types','UserController@UserTypes')->name('UserTypes');
        Route::get('/new-user-types','UserController@newUserTypes')->name('New.UserTypes');
        Route::post('/new-user-types','UserController@newUserTypesPost')->name('New.UserTypes');
        Route::get('/users','UserController@Users')->name('Users');
        Route::get('/new-user','UserController@newUser')->name('New.User');
        Route::post('/new-user','UserController@newUserPost')->name('New.User');
    });
});
