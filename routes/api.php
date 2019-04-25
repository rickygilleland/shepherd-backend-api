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

//app version <=1.0.4 routes

Route::middleware('auth:api')->post('/user', 'UsersController@get_user', function ($request) {
	
});

Route::middleware('auth:api')->post('/posts', 'PostsController@get_posts', function ($request) {
	
});

Route::middleware('auth:api')->post('/post', 'PostsController@add_post', function ($request) {
	
});

Route::middleware('auth:api')->post('/post/get', 'PostsController@get_post', function ($request) {
	
});

Route::middleware('auth:api')->post('/post/report', 'PostsController@report_post', function ($request) {
	
});

Route::middleware('auth:api')->post('/post/delete', 'PostsController@delete_post', function ($request) {
	
});


Route::middleware('auth:api')->post('/post/comments', 'PostsController@get_post_comments', function ($request) {
	
});


Route::middleware('auth:api')->post('/comment', 'PostsController@add_comment', function ($request) {
	
});

Route::middleware('auth:api')->post('/comment/report', 'PostsController@report_comment', function ($request) {
	
});

Route::middleware('auth:api')->post('/comment/delete', 'PostsController@delete_comment', function ($request) {
	
});


Route::middleware('auth:api')->post('/vote', 'PostsController@vote', function ($request) {
	
});

//app version >1.1.0 routes
Route::middleware(['jwt'])->post('/1.1.0/user', 'UsersController@get_user', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/user/profile_complete', 'UsersController@check_if_profile_complete', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/posts', 'PostsController@get_posts', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/post', 'PostsController@add_post', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/post/get', 'PostsController@get_post', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/post/report', 'PostsController@report_post', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/post/delete', 'PostsController@delete_post', function ($request) {
	
});


Route::middleware(['jwt'])->post('/1.1.0/post/comments', 'PostsController@get_post_comments', function ($request) {
	
});


Route::middleware(['jwt'])->post('/1.1.0/comment', 'PostsController@add_comment', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/comment/report', 'PostsController@report_comment', function ($request) {
	
});

Route::middleware(['jwt'])->post('/1.1.0/comment/delete', 'PostsController@delete_comment', function ($request) {
	
});


Route::middleware(['jwt'])->post('/1.1.0/vote', 'PostsController@vote', function ($request) {
	
});
