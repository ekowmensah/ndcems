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
    /*
    ==========================================================
                  Sms Api Routes
    ==========================================================
     */

        Route::any('/new-message','MessageHandler@handleNew');

     /*
    ==========================================================
            end      Sms Api Routes
    ==========================================================
     */

    Route::any('/notify_me','MessageHandler@handle');
//Route::group(['middleware' => ['auth']], function () {
    Route::middleware(['auth:superAdmin', 'admin.reauth', 'governance.audit:superAdmin'])->post('/command', function () {
        abort_unless(app()->environment('local'), 404);
        Artisan::call('migrate', ['--force' => true]);
        return response()->json(['status' => 'ok', 'message' => 'Migration executed.']);
    })->name('maintenance.migrate');
    Route::get('/', function () {
        return view('public.landing');
    })->name('landing');
    Route::get('parliament/{id?}','PublicController@parliament')->name('parliament');
    Route::get('president/{id?}','PublicController@president')->name('president');
    Route::post('result-ajax','PublicController@ajaxResult')->name('ajaxResult');
    Route::post('result-count-ajax','PublicController@ajaxCountResult')->name('ajaxCountResult');

    Route::post('/get-constituency','PublicController@getConstituency')->name('getConstituency');
    Route::post('/get-electral','PublicController@getElectral')->name('getElectral');
    Route::post('/get-polling-station','PublicController@getPollingStation')->name('getPollingStation');
//});
Route::group(['middleware' => ['auth','user_type']], function () {
    Route::get('/home', 'HomeController@index')->name('home');
});
Route::group(['prefix' =>"agent",'namespace'=>'Agent','as' => 'Agent.'], function () {
    Route::group(['middleware' => ['auth','user.type:5','can:capture-results','governance.audit:web']], function () {
        Route::get('/election',  'AgentController@election')->name("election");
        Route::post('/election',  'AgentController@electionPost')->name("electionPost");
        Route::get('/home/{election_start_up}/{election_result_id?}',  'AgentController@index')->name("Home");
        Route::post('/result-capture/{election_start_up}',  'AgentController@captureResult')->name("CaptureResult");
        Route::post('/pink-sheet-upload/{election_start_up}',  'AgentController@uploadPinkSheet')->name("UploadPinkSheet");
        Route::get('/pink-sheet/{election_result_id}',  'AgentController@viewPinkSheet')->name("ViewPinkSheet");
        Route::get('/results',  'AgentController@results')->name("results");
        Route::get('/view-result/{election_start_up}/{election_result_id?}',  'AgentController@viewResults')->name("viewResults");
    });
});

Auth::routes();
Route::group(['prefix' => 'operations', 'namespace' => 'Operations', 'as' => 'Operations.', 'middleware' => ['auth', 'user.type:1,2,3', 'governance.audit:web']], function () {
    Route::get('/incidents', 'IncidentController@index')->name('incidents');
    Route::patch('/incidents/{id}', 'IncidentController@update')->name('incidents.update');
    Route::get('/notifications', 'NotificationController@index')->name('notifications');
    Route::post('/notifications/read-all', 'NotificationController@readAll')->name('notifications.readAll');
    Route::post('/notifications/{id}/read', 'NotificationController@read')->name('notifications.read');
    Route::get('/reports', 'SituationReportController@index')->name('reports');
    Route::post('/reports', 'SituationReportController@generate')->name('reports.generate');
    Route::post('/reports/{id}/download-link', 'SituationReportController@downloadLink')->name('reports.downloadLink');
    Route::get('/reports/{id}/download', 'SituationReportController@download')->middleware('signed')->name('reports.download');
});
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

    Route::group(['middleware' => ['auth:superAdmin', 'governance.audit:superAdmin']], function () {


        Route::get('/admin-users','HomeController@admin')->name('admin');
        Route::get('/security/confirm-password','SecurityController@showConfirmPassword')->name('security.confirm');
        Route::post('/security/confirm-password','SecurityController@confirmPassword')->name('security.confirm.store');
        Route::get('/governance/audits','GovernanceController@index')->name('governance.audits');
        Route::get('/governance/archived-accounts','GovernanceController@archivedAccounts')->name('governance.archived');
        Route::patch('/governance/archived-accounts/{type}/{id}','GovernanceController@restoreAccount')->middleware('admin.reauth')->name('governance.restore');
        Route::get('/imports','ImportController@index')->name('imports.index');
        Route::post('/imports/preview','ImportController@preview')->name('imports.preview');
        Route::get('/imports/{batch}','ImportController@show')->name('imports.show');
        Route::post('/imports/{batch}/commit','ImportController@commit')->middleware('admin.reauth')->name('imports.commit');
        Route::delete('/imports/{batch}/rollback','ImportController@rollback')->middleware('admin.reauth')->name('imports.rollback');
        Route::get('/admin-user/add','HomeController@newAdmin')->name('New.Admin');
        Route::get('/admin-user/edit/{id}','HomeController@editAdmin')->name('edit.Admin');
        Route::post('/admin-user/edit/{id}/post','HomeController@editAdminPost')->middleware('admin.reauth')->name('edit.AdminPost');
        Route::delete('/admin-user/{id}','HomeController@deleteAdmin')->middleware('admin.reauth')->name('delete.Admin');
        Route::post('/admin-user/add/post','HomeController@newAdminPost')->middleware('admin.reauth')->name('newAdminPost');


        Route::get('/','HomeController@index')->name('home');
        Route::get('/result/{id}','HomeController@result')->name('result');
        Route::get('/result/{id}/report','HomeController@resultReport')->name('resultReport');
        Route::post('/result/{id}/report/post','HomeController@resultReportPost')->name('resultReportPost');

        Route::post('presidential-result-ajax/','HomeController@presidentialResultAjax')->name('presidentialResultAjax');
        Route::post('all-result-ajax/','HomeController@allResultAjax')->name('allResultAjax');

        Route::get('/parliamentary-result','HomeController@parliamentaryResult')->name('parliamentaryResult');
        //Route::get('/parliamentary-result-ajax','HomeController@parliamentaryResultAjax')->name('parliamentaryResultAjax');
        Route::get('/constituency/result/{id}','HomeController@constituencyView')->name('constituencyView');


        Route::get('/manager-types','UserController@UserTypes')->name('UserTypes');
        Route::get('/new-manager-types','UserController@newUserTypes')->name('New.UserTypes');
        Route::get('/manager-types-edit/{id}','UserController@UserTypesEdit')->name('UserTypesEdit');
        Route::post('/manager-types-edit','UserController@UserTypesEditPost')->name('Edit.UserTypes');
        Route::delete('/manager-types/{id}','UserController@UserTypesDelete')->middleware('admin.reauth')->name('UserTypesDelete');

        Route::post('/new-manager-types','UserController@newUserTypesPost')->name('New.UserTypes');
        Route::get('/managers/{id?}','UserController@Users')->name('Users');
        Route::get('/new-manager/{type_id}','UserController@newUser')->name('New.User');
        Route::post('/new-manager','UserController@newUserPost')->name('New.UserPost');

        Route::post('/verify-username','UserController@VerifyUsername')->name('VerifyUsername');

        Route::get('/managers-edit/{id}','UserController@UsersEdit')->name('UsersEdit');
        Route::delete('/managers/{id}','UserController@UsersDelete')->middleware('admin.reauth')->name('UsersDelete');

        Route::post('/managers-edit/','UserController@EditUserPost')->name('EditUserPost');

        Route::get('/polling-agent/','UserController@pollingAgent')->name('pollingAgent');
        Route::get('/polling-agent-ajax/','UserController@pollingAgentAjax')->name('pollingAgentAjax');

        Route::get('/management-user-ajax/','UserController@managementAgentAjax')->name('managementAgentAjax');


        Route::get('/country/','ContentController@country')->name('country');
        Route::get('/country/add/','ContentController@countryAdd')->name('New.Country');
        Route::post('/country/add/','ContentController@countryAddPost')->name('New.Country');
        Route::get('/country/edit/{id}','ContentController@countryEdit')->name('countryEdit');
        Route::post('/country/edit/{id}','ContentController@countryEditPost')->name('countryEdit');
        Route::delete('/country/{id}','ContentController@countryDelete')->middleware('admin.reauth')->name('countryDelete');

        Route::get('/region/','ContentController@region')->name('region');
        Route::get('/region/add/','ContentController@regionAdd')->name('New.region');
        Route::post('/region/add/','ContentController@regionAddPost')->name('New.Region');
        Route::get('/region/edit/{id}','ContentController@regionEdit')->name('regionEdit');
        Route::post('/region/edit/{id}','ContentController@regionEditPost')->name('regionEdit');
        Route::delete('/region/{id}','ContentController@regionDelete')->middleware('admin.reauth')->name('regionDelete');

        Route::get('/constituency/','ContentController@constituency')->name('constituency');
        Route::get('/constituency-ajax/','ContentController@constituencyAajax')->name('constituencyAajax');

        Route::get('/constituency/add/','ContentController@constituencyAdd')->name('New.constituency');
        Route::post('/constituency/add/','ContentController@constituencyAddPost')->name('New.constituency');
        Route::get('/constituency/edit/{id}','ContentController@constituencyEdit')->name('constituencyEdit');
        Route::post('/constituency/edit/{id}','ContentController@constituencyEditPost')->name('constituencyEdit');
        Route::delete('/constituency/{id}','ContentController@constituencyDelete')->middleware('admin.reauth')->name('constituencyDelete');




        Route::post('/get-regions','ContentController@getRegion')->name('getRegion');
        Route::get('/regions-ajax','ContentController@regionAjax')->name('regionAjax');


        Route::get('/electoral-area/','ContentController@ElectoralArea')->name('ElectoralArea');
        Route::get('/electoral-area-ajax/','ContentController@electralAajax')->name('electralAajax');

        Route::get('/electoral-area/add/','ContentController@ElectoralAreaAdd')->name('New.ElectoralArea');
        Route::post('/electoral-area/add/','ContentController@ElectoralAreaAddPost')->name('New.ElectoralArea');
        /* Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEdit'); */
        Route::delete('/electoral-area/{id}','ContentController@ElectoralAreaDelete')->middleware('admin.reauth')->name('ElectoralAreaDelete');
        Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}/post','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEditPost');

        Route::post('/get-constituency','ContentController@getConstituency')->name('getConstituency');
        Route::post('/get-political-party','ContentController@getPoliticalParty')->name('getPoliticalParty');
        Route::post('/get-political-party-by-election','ContentController@getPoliticalPartyByElectionType')->name('getPoliticalPartyByElectionType');



        Route::get('/polling-station/','ContentController@PollingStation')->name('PollingStation');
        Route::get('/polling-station-ajax/','ContentController@pollingStationAajax')->name('pollingStationAajax');

        Route::get('/polling-station/add/','ContentController@PollingStationAdd')->name('New.PollingStation');
        Route::post('/polling-station/add/','ContentController@PollingStationAddPost')->name('New.PollingStation');
        /* Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEdit'); */
        Route::delete('/polling-station/{id}','ContentController@PollingStationDelete')->middleware('admin.reauth')->name('PollingStationDelete');
        Route::get('/polling-station/edit/{id?}','ContentController@PollingStationEdit')->name('PollingStationEdit');
        Route::post('/polling-station/edit/{id}/post','ContentController@PollingStationEditPost')->name('PollingStationEditPost');

        Route::post('/get-electral','ContentController@getElectral')->name('getElectral');
        Route::post('/get-polling-station','ContentController@getPollingStation')->name('getPollingStation');


        Route::get('/election-type/','ElectionController@electionType')->name('electionType');
        Route::get('/election-type/add','ElectionController@newElectionType')->name('New.electionType');
        Route::post('/election-type/add','ElectionController@newElectionTypePost')->name('New.electionType');
        Route::get('/election-type/edit/{id}','ElectionController@electionTypesEdit')->name('electionTypesEdit');
        Route::post('/election-type/edit/{id}','ElectionController@electionTypesEditPost')->name('electionTypesEdit');
        Route::delete('/election-type/{id}','ElectionController@electionTypesDelete')->middleware('admin.reauth')->name('electionTypesDelete');

        Route::get('/political-party/','ElectionController@politicalParty')->name('politicalParty');
        Route::get('/political-party-ajax/','ElectionController@politicalPartyAjax')->name('politicalPartyAjax');
        Route::get('/political-party/add','ElectionController@newPoliticalParty')->name('New.politicalParty');
        Route::post('/political-party/add','ElectionController@newPoliticalPartyPost')->name('New.politicalParty');
        Route::get('/political-party/edit/{id?}','ElectionController@editPoliticalParty')->name('Edit.politicalParty');
        Route::post('/political-party/edit/{id}','ElectionController@editPoliticalPartyPost')->name('Edit.editPoliticalPartyPost');
        Route::delete('/political-party/{id}','ElectionController@DeletePoliticalParty')->middleware('admin.reauth')->name('delete.politicalParty');

        Route::get('/candidate/{id?}','CandidateController@candidate')->name('candidate');
        Route::get('/candidate/register/{id}','CandidateController@candidateRegister')->name('candidateRegister');
        Route::post('/candidate/register/candidate','CandidateController@candidateRegisterPost')->name('candidateRegisterPost');
        Route::post('verify-positioning-ordering','CandidateController@VerifyPositioningOrdering')->name('VerifyPositioningOrdering');

        Route::get('/candidate/edit/{id?}','CandidateController@candidateEdit')->name('candidateEdit');
        Route::post('/candidate/edit/{id?}/post','CandidateController@candidateEditPost')->name('candidateEditPost');
        Route::delete('/candidate/{id}','CandidateController@candidateDelete')->middleware('admin.reauth')->name('candidateDelete');


        Route::get('/election-ajax/','CandidateController@candidateAjax')->name('candidateAjax');

        Route::get('/election/','ElectionController@election')->name('election');
        Route::get('/election/new','ElectionController@electionNew')->name('electionNew');
        Route::get('/election/detail/{id}','ElectionController@electionDetail')->name('electionDetail');
        Route::post('/election/detail/{id}/post','ElectionController@electionDetailPost')->name('electionDetailPost');
        Route::post('/election/detail/post','ElectionController@electionNewPost')->name('electionNewPost');
        Route::patch('/election/detail/{id}/status/{tougle}','ElectionController@electionDetailTougle')->middleware('admin.reauth')->name('electionDetailTougle');


    });
});


Route::group(['prefix' =>"director",'namespace'=>'Director','as' => 'Director.'], function () {
    Route::group(['middleware' => ['auth','user.type:3','governance.audit:web']], function () {
        Route::get('/election',  'AgentController@election')->name("election");
        Route::post('/election',  'AgentController@electionPost')->name("electionPost");
        Route::get('/home/{election_start_up}/{polling_station?}/{election_result_id?}',  'AgentController@index')->name("Home");
        Route::post('/result-capture/{election_start_up}',  'AgentController@captureResult')->name("CaptureResultSelf");


        Route::get('/','HomeController@index')->name('home');
        Route::get('/dashboard-data','HomeController@dashboardData')->name('dashboardData');
        Route::get('/collation','HomeController@collation')->name('collation');
        Route::get('/collation-data','HomeController@collationData')->name('collationData');
        Route::get('/polling-agent','PollingAgentController@pollingAgent')->name('pollingAgent');
        Route::get('/polling-agent-ajax','PollingAgentController@pollingAgentAjax')->name('pollingAgentAjax');

        Route::get('/polling-agent-edit/{id}','PollingAgentController@UsersEdit')->name('UsersEdit');
        Route::delete('/polling-agent/{id}','PollingAgentController@UsersDelete')->name('UsersDelete');

        Route::post('/polling-agent-edit/','PollingAgentController@EditUserPost')->name('EditUserPost');

        Route::post('/get-constituency','PollingAgentController@getConstituency')->name('getConstituency');
        Route::post('/verify-username','PollingAgentController@VerifyUsername')->name('VerifyUsername');
        Route::post('/get-regions','PollingAgentController@getRegion')->name('getRegion');
        Route::post('/get-electral','PollingAgentController@getElectral')->name('getElectral');
        Route::post('/get-polling-station','PollingAgentController@getPollingStation')->name('getPollingStation');


        Route::get('/polling-agent/{type_id}','PollingAgentController@newUser')->name('New.User');
        Route::post('/polling-agent-post','PollingAgentController@newUserPost')->name('New.UserPost');

        Route::get('/candidate/','CandidateController@candidate')->name('candidate');
        Route::get('/profile/','CandidateController@profile')->name('profile');

        Route::get('/election-ajax/','CandidateController@candidateAjax')->name('candidateAjax');

        Route::get('/candidate/register/','CandidateController@candidateRegister')->name('candidateRegister');
        Route::post('/candidate/register/candidate','CandidateController@candidateRegisterPost')->name('candidateRegisterPost');
        Route::get('/candidate/edit/{id?}','CandidateController@candidateEdit')->name('candidateEdit');
        Route::post('/candidate/edit/{id?}/post','CandidateController@candidateEditPost')->name('candidateEditPost');
        Route::delete('/candidate/{id}','CandidateController@candidateDelete')->name('candidateDelete');

        Route::get('/electoral-area/','ContentController@ElectoralArea')->name('ElectoralArea');
        Route::get('/electoral-area-ajax/','ContentController@electralAajax')->name('electralAajax');
        Route::get('/electoral-area/add/','ContentController@ElectoralAreaAdd')->name('New.ElectoralArea');
        Route::post('/electoral-area/add/','ContentController@ElectoralAreaAddPost')->name('New.ElectoralArea');
        Route::delete('/electoral-area/{id}','ContentController@ElectoralAreaDelete')->name('ElectoralAreaDelete');
        Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}/post','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEditPost');

        Route::get('/polling-station/','ContentController@PollingStation')->name('PollingStation');
        Route::get('/polling-station-ajax/','ContentController@pollingStationAajax')->name('pollingStationAajax');
        Route::get('/polling-station/add/','ContentController@PollingStationAdd')->name('New.PollingStation');
        Route::post('/polling-station/add/','ContentController@PollingStationAddPost')->name('New.PollingStation');
        Route::delete('/polling-station/{id}','ContentController@PollingStationDelete')->name('PollingStationDelete');
        Route::get('/polling-station/edit/{id?}','ContentController@PollingStationEdit')->name('PollingStationEdit');
        Route::post('/polling-station/edit/{id}/post','ContentController@PollingStationEditPost')->name('PollingStationEditPost');

        Route::post('/get-political-party','ContentController@getPoliticalParty')->name('getPoliticalParty');


        Route::get('/result','PollingAgentController@result')->name('Result');
        Route::get('/result-analytics','PollingAgentController@resultAnalytics')->name('ResultAnalytics');
        Route::get('/result-detail/{id}','PollingAgentController@resultDetail')->name('ResultDetail');
        Route::delete('/result/{id}','PollingAgentController@deleteResults')->name('deleteResults');
        Route::post('/pink-sheet-upload/{election_result_id}','PollingAgentController@uploadPinkSheet')->name('UploadPinkSheet');
        Route::get('/pink-sheet/{election_result_id}','PollingAgentController@viewPinkSheet')->name('ViewPinkSheet');
        Route::get('/pink-sheet/{election_result_id}/download','PollingAgentController@downloadPinkSheet')->name('DownloadPinkSheet');

        Route::get('/polling-station-result-ajax','PollingAgentController@pollingStationResultAajax')->name('pollingStationResultAajax');
        ///Route::get('/result/{id}','PollingAgentController@constituencyView')->name('constituencyView');
        Route::get('/view-result/{election_start_up}/{election_result_id?}',  'PollingAgentController@viewResults')->name("viewResults");
        Route::post('/result/{id}/decision','PollingAgentController@decideResult')->middleware('can:approve-results')->name('confirmResults');

        Route::get('/edit-result/{election_start_up}/{election_result_id?}/{user_id?}',  'PollingAgentController@editResult')->name("editResult");
        Route::post('/result-capture/{election_start_up}/{user_id?}',  'PollingAgentController@captureResult')->name("CaptureResult");

        //        Route::get('/region-result',  'ContentController@Presidential')->name("Regional");
        Route::get('/result-xlx/{election_start_up}/{election_result_id?}',  'PollingAgentController@resultsXlx')->name("resultsXlx");

        Route::post('verify-positioning-ordering','CandidateController@VerifyPositioningOrdering')->name('VerifyPositioningOrdering');


    });
});

Route::group(['prefix' =>"region",'namespace'=>'Region','as' => 'Region.'], function () {
    Route::group(['middleware' => ['auth','user.type:2','can:review-regional-results','governance.audit:web']], function () {
        Route::get('/dashboard',  'ContentController@dashboard')->name("dashboard");
        Route::get('/profile',  'ContentController@profile')->name("profile");
        Route::post('presidential-result-ajax/','ContentController@presidentialResultAjax')->name('presidentialResultAjax');
        Route::get('/region-result',  'ContentController@Presidential')->name("Regional");
        Route::get('/constituency-ajax/','ContentController@constituencyAajax')->name('constituencyAajax');
        Route::get('/constituency/result/{id}','ContentController@constituencyView')->name('constituencyView');
        Route::get('/regional/result/{id}/{regional_id?}','ContentController@regionalResultView')->name('regionalResultView');
        Route::get('/presidential-result',  'ContentController@PresidentialResult')->name("Presidential");
        Route::get('presidential-ajax/','ContentController@PresidentialAajax')->name('PresidentialAajax');
        Route::get('/result-review','ContentController@resultReview')->name('resultReview');
        Route::get('/result-review/{id}','ContentController@resultReviewDetail')->name('resultReviewDetail');
        Route::get('/result-review/{id}/pink-sheet','ContentController@reviewPinkSheet')->name('reviewPinkSheet');
        Route::post('/result-review/{id}/escalations','ContentController@storeEscalation')->name('storeEscalation');
        Route::post('/result-review/escalations/{id}/resolve','ContentController@resolveEscalation')->name('resolveEscalation');
    });
});
Route::group(['prefix' =>"national",'namespace'=>'National','as' => 'National.'], function () {
    Route::group(['middleware' => ['auth','user.type:1','can:review-national-results','governance.audit:web']], function () {
        Route::get('/dashboard',  'ContentController@dashboard')->name("dashboard");
        Route::get('/dashboard/chart-data',  'ContentController@presidentialResultAjax')->name("dashboardChart");
        Route::get('/dashboard/map-data',  'ContentController@dashboardMap')->name("dashboardMap");
        Route::get('/profile',  'ContentController@profile')->name("profile");

        Route::get('/presidential-result',  'ContentController@PresidentialResult')->name("Presidential");
        Route::get('presidential-ajax/','ContentController@PresidentialAajax')->name('PresidentialAajax');
        Route::get('/regional/result/{id}/{regional_id}',  'ContentController@regionalResultView')->name('regionalResultView');

        Route::get('/constituency-result',  'ContentController@ConstituencyResult')->name("ConstituencyResult");
        Route::get('constituency-result-ajax/','ContentController@ConstituencyResultAajax')->name('ConstituencyResultAajax');
        Route::get('/constituency/result/{id}','ContentController@constituencyView')->name('constituencyView');
        Route::get('/result-review','ContentController@resultReview')->name('resultReview');
        Route::get('/result-review/{id}','ContentController@resultReviewDetail')->name('resultReviewDetail');
        Route::get('/result-review/{id}/pink-sheet','ContentController@reviewPinkSheet')->name('reviewPinkSheet');
        Route::post('/result-review/{id}/escalations','ContentController@storeEscalation')->name('storeEscalation');
        Route::post('/result-review/escalations/{id}/resolve','ContentController@resolveEscalation')->name('resolveEscalation');

        Route::get('/polling-agent/','ContentController@pollingAgent')->name('pollingAgent');
        Route::get('/polling-agent-ajax/','ContentController@pollingAgentAjax')->name('pollingAgentAjax');

        Route::post('/get-constituency','ContentController@getConstituency')->name('getConstituency');
        Route::post('/get-regions','ContentController@getRegion')->name('getRegion');
        Route::post('/get-electral','ContentController@getElectral')->name('getElectral');
        Route::post('/get-polling-station','ContentController@getPollingStation')->name('getPollingStation');

        Route::get('/candidate/{id?}','ContentController@candidate')->name('candidate');
        Route::get('/election-ajax/','ContentController@candidateAjax')->name('candidateAjax');

        Route::get('/managers/','UserController@Users')->name('Users');
        Route::get('/management-user-ajax/','UserController@managementAgentAjax')->name('managementAgentAjax');
        Route::get('/new-manager/{type_id}','UserController@newUser')->name('New.User');
        Route::post('/new-manager','UserController@newUserPost')->name('New.UserPost');
        Route::get('/managers-edit/{id}','UserController@UsersEdit')->name('UsersEdit');
        Route::delete('/managers/{id}','UserController@UsersDelete')->name('UsersDelete');
        Route::post('/managers-edit/','UserController@EditUserPost')->name('EditUserPost');

        Route::get('/electoral-area/','ContentController@ElectoralArea')->name('ElectoralArea');
        Route::get('/electoral-area-ajax/','ContentController@electralAajax')->name('electralAajax');

        /* Route::get('/electoral-area/add/','ContentController@ElectoralAreaAdd')->name('New.ElectoralArea');
        Route::post('/electoral-area/add/','ContentController@ElectoralAreaAddPost')->name('New.ElectoralArea');
        Route::get('/electoral-area/delete/{id}','ContentController@ElectoralAreaDelete')->name('ElectoralAreaDelete');
        Route::get('/electoral-area/edit/{id}','ContentController@ElectoralAreaEdit')->name('ElectoralAreaEdit');
        Route::post('/electoral-area/edit/{id}/post','ContentController@ElectoralAreaEditPost')->name('ElectoralAreaEditPost');
        Route::post('/get-constituency','ContentController@getConstituency')->name('getConstituency'); */


        Route::get('/polling-station/','ContentController@PollingStation')->name('PollingStation');
        Route::get('/polling-station-ajax/','ContentController@pollingStationAajax')->name('pollingStationAajax');

        /* Route::get('/polling-station/add/','ContentController@PollingStationAdd')->name('New.PollingStation');
        Route::post('/polling-station/add/','ContentController@PollingStationAddPost')->name('New.PollingStation');
        Route::get('/polling-station/delete/{id?}','ContentController@PollingStationDelete')->name('PollingStationDelete');
        Route::get('/polling-station/edit/{id?}','ContentController@PollingStationEdit')->name('PollingStationEdit');
        Route::post('/polling-station/edit/{id}/post','ContentController@PollingStationEditPost')->name('PollingStationEditPost'); */


        Route::get('/result/{id}','ContentController@result')->name('result');
        Route::post('all-result-ajax/','ContentController@allResultAjax')->name('allResultAjax');

        Route::post('/verify-username','UserController@VerifyUsername')->name('VerifyUsername');

        Route::get('/candidate/register/{id}','CandidateController@candidateRegister')->name('candidateRegister');
        Route::post('/candidate/register/candidate','CandidateController@candidateRegisterPost')->name('candidateRegisterPost');
        Route::get('/candidate/edit/{id?}','CandidateController@candidateEdit')->name('candidateEdit');
        Route::post('/candidate/edit/{id?}/post','CandidateController@candidateEditPost')->name('candidateEditPost');
        Route::delete('/candidate/{id}','CandidateController@candidateDelete')->name('candidateDelete');
        Route::post('/get-political-party-by-election','ContentController@getPoliticalPartyByElectionType')->name('getPoliticalPartyByElectionType');
        Route::post('/get-political-party','ContentController@getPoliticalParty')->name('getPoliticalParty');

    });
});
