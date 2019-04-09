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

Route::post('/token', 'ApiTokenController@token', function($request) {
	
});

Route::middleware('auth:api')->post('/user', 'UsersController@get_user', function ($request) {
	
});

Route::middleware('auth:api')->post('/posts', 'PostsController@get_posts', function ($request) {
	
});

Route::middleware('auth:api')->post('/post', 'PostsController@add_post', function ($request) {
	
});


Route::middleware('auth:api')->post('/vote', 'PostsController@vote', function ($request) {
	
});


