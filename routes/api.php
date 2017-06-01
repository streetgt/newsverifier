<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/news', 'ApiController@call')->name('api.news');
Route::get('/news/upvote', 'ApiController@upvote')->name('api.news.upvote');
Route::get('/news/downvote', 'ApiController@downvote')->name('api.news.downvote');
