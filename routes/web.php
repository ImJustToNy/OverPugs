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

Route::get('/', 'HomeController@home')->name('home');

Route::get('match/{id}', 'MatchController@getMatch')->name('getMatch');

Route::group(['prefix' => 'api'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::post('changeRegion', 'UserController@changeRegion');

        Route::group(['prefix' => 'match'], function () {
            Route::post('add', 'MatchController@addMatch');
            Route::post('refresh', 'MatchController@refreshMatch');
            Route::post('delete', 'MatchController@deleteMatch');
        });
    });

    Route::get('match/list', 'MatchController@getAvailable');
});

Route::get('login', 'LoginController@login')->name('login');
Route::get('login/discord', 'LoginController@loginDiscord')->name('loginDiscord');

Route::get('endpoint', 'LoginController@endpoint');
Route::get('endpoint/discord', 'LoginController@endpointDiscord');

Route::get('logout', 'LoginController@logout')->name('logout');
