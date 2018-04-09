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

Route::group(['prefix' => ""], function () {
    Route::get('/', 'InformerController@getWelcomePage');
    Route::post('/request', 'InformerController@request');
    Route::post('/install', 'InformerController@install');
    Route::get('/config', 'InformerController@getInstallationMessage');
    Route::get('/home', 'InformerController@getHomePage');
    Route::get("/contacts", 'InformerController@getContactList');
    Route::get("/candidates", 'InformerController@getCandidateList');
    Route::post('/send', 'InformerController@sendRequest');
    Route::get('/here', 'InformerController@send');

});
/*Route::get('/', function () {
    return view('welcome');
});

Route::post('/request', 'Inform')*/

