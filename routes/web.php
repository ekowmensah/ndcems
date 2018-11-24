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
Route::group(['middleware' => ['auth']], function () {
        Route::get('/', function () {
            return redirect(route('home'));
    });
    Route::get('/home', 'HomeController@index')->name('home');
});
Auth::routes();



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
        Route::get('/manager-types','UserController@UserTypes')->name('UserTypes');
        Route::get('/new-manager-types','UserController@newUserTypes')->name('New.UserTypes');
        Route::get('/manager-types-edit/{id}','UserController@UserTypesEdit')->name('UserTypesEdit');
        Route::post('/manager-types-edit','UserController@UserTypesEditPost')->name('Edit.UserTypes');
        Route::get('/manager-types-delete/{id}','UserController@UserTypesDelete')->name('UserTypesDelete');

        Route::post('/new-manager-types','UserController@newUserTypesPost')->name('New.UserTypes');
        Route::get('/managers','UserController@Users')->name('Users');
        Route::get('/new-manager/{type_id}','UserController@newUser')->name('New.User');
        Route::post('/new-manager','UserController@newUserPost')->name('New.UserPost');

        Route::post('/verify-username','UserController@VerifyUsername')->name('VerifyUsername');
    });
});
