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

Route::get('test', 'ApiController@callGET');

//Route::post('external', 'ExternalController@call')->name('external');
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', 'HomeController@index');
    Route::get('/home', 'HomeController@index');

    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('/datatables/news', 'HomeController@news')->name('datatables.news');
    Route::get('verify/{id}','HomeController@verify')->name('verify');
});
