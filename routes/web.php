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
//Route::group(['middleware' => ['auth']], function () {
        Route::get('/', function () {
            return redirect(route('home'));
    });

//});
Route::get('/home', 'HomeController@index')->name('home');
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

        Route::get('/managers-edit/{id}','UserController@UsersEdit')->name('UsersEdit');
        Route::post('/managers-edit/','UserController@EditUserPost')->name('EditUserPost');

        Route::get('/country/','ContentController@country')->name('country');
        Route::get('/country/add/','ContentController@countryAdd')->name('New.Country');
        Route::post('/country/add/','ContentController@countryAddPost')->name('New.Country');
        Route::get('/country/edit/{id}','ContentController@countryEdit')->name('countryEdit');
        Route::post('/country/edit/{id}','ContentController@countryEditPost')->name('countryEdit');
        Route::get('/country/delete/{id}','ContentController@countryDelete')->name('countryDelete');

        Route::get('/region/','ContentController@region')->name('region');
        Route::get('/region/add/','ContentController@regionAdd')->name('New.region');
        Route::post('/region/add/','ContentController@regionAddPost')->name('New.Region');
        Route::get('/region/edit/{id}','ContentController@regionEdit')->name('regionEdit');
        Route::post('/region/edit/{id}','ContentController@regionEditPost')->name('regionEdit');
        Route::get('/region/delete/{id}','ContentController@regionDelete')->name('regionDelete');

        Route::get('/constituency/','ContentController@constituency')->name('constituency');
        Route::get('/constituency-ajax/','ContentController@constituencyAajax')->name('constituencyAajax');

        Route::get('/constituency/add/','ContentController@constituencyAdd')->name('New.constituency');
        Route::post('/constituency/add/','ContentController@constituencyAddPost')->name('New.constituency');
        Route::get('/constituency/edit/{id}','ContentController@constituencyEdit')->name('constituencyEdit');
        Route::post('/constituency/edit/{id}','ContentController@constituencyEditPost')->name('constituencyEdit');
        Route::get('/constituency/delete/{id}','ContentController@constituencyDelete')->name('constituencyDelete');


        Route::post('/get-regions','ContentController@getRegion')->name('getRegion');

        Route::get('/electoral-area/','ContentController@ElectoralArea')->name('ElectoralArea');
        Route::get('/electoral-area-ajax/','ContentController@electralAajax')->name('electralAajax');

        Route::get('/electoral-area/add/','ContentController@ElectoralAreaAdd')->name('New.ElectoralArea');
        Route::post('/electoral-area/add/','ContentController@ElectoralAreaAddPost')->name('New.ElectoralArea');
        /* Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEdit'); */
        Route::get('/electoral-area/delete/{id}','ContentController@ElectoralAreaDelete')->name('ElectoralAreaDelete');

        Route::post('/get-constituency','ContentController@getConstituency')->name('getConstituency');


        Route::get('/polling-station/','ContentController@PollingStation')->name('PollingStation');
        Route::get('/polling-station-ajax/','ContentController@pollingStationAajax')->name('pollingStationAajax');

        Route::get('/polling-station/add/','ContentController@PollingStationAdd')->name('New.PollingStation');
        Route::post('/polling-station/add/','ContentController@PollingStationAddPost')->name('New.PollingStation');
        /* Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEdit'); */
        Route::get('/polling-station/delete/{id?}','ContentController@PollingStationDelete')->name('PollingStationDelete');

        Route::post('/get-electral','ContentController@getElectral')->name('getElectral');
        Route::post('/get-polling-station','ContentController@getPollingStation')->name('getPollingStation');


        Route::get('/election-type/','ElectionController@electionType')->name('electionType');
        Route::get('/election-type/add','ElectionController@newElectionType')->name('New.electionType');
        Route::post('/election-type/add','ElectionController@newElectionTypePost')->name('New.electionType');
        Route::get('/election-type/edit/{id}','ElectionController@electionTypesEdit')->name('electionTypesEdit');
        Route::post('/election-type/edit/{id}','ElectionController@electionTypesEditPost')->name('electionTypesEdit');
        Route::get('/election-type/delete/{id}','ElectionController@electionTypesDelete')->name('electionTypesDelete');

        Route::get('/political-party/','ElectionController@politicalParty')->name('politicalParty');
        Route::get('/political-party-ajax/','ElectionController@politicalPartyAjax')->name('politicalPartyAjax');
        Route::get('/political-party/add','ElectionController@newPoliticalParty')->name('New.politicalParty');
        Route::post('/political-party/add','ElectionController@newPoliticalPartyPost')->name('New.politicalParty');
        Route::get('/political-party/edit/{id?}','ElectionController@editPoliticalParty')->name('Edit.politicalParty');
        Route::post('/political-party/edit/{id}','ElectionController@editPoliticalPartyPost')->name('Edit.editPoliticalPartyPost');
        Route::get('/political-party/delete/{id?}','ElectionController@DeletePoliticalParty')->name('delete.politicalParty');

        Route::get('/candidate/','CandidateController@candidate')->name('candidate');
        Route::get('/candidate/register/{id}','CandidateController@candidateRegister')->name('candidateRegister');
        Route::post('/candidate/register/candidate','CandidateController@candidateRegisterPost')->name('candidateRegisterPost');
        Route::get('/candidate/edit/{id?}','CandidateController@candidateEdit')->name('candidateEdit');
        Route::post('/candidate/edit/{id?}/post','CandidateController@candidateEditPost')->name('candidateEditPost');
        Route::get('/candidate/delete/{id?}','CandidateController@candidateDelete')->name('candidateDelete');


        Route::get('/election-ajax/','CandidateController@candidateAjax')->name('candidateAjax');

    });
});
