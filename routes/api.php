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
Route::group([
    'prefix'=>'user',
    'namespace'=>'User',
],
function(){
    Route::post('register','AuthController@register');
    Route::post('login','AuthController@login');

    Route::post("category/add","CategoryController@add_category");
    Route::get("category/get-all/{token}/{pagination?}","CategoryController@get_paginate_category");
    Route::get("category/get-single/{id}","CategoryController@get_category");
    Route::post("category/update/{id}","CategoryController@update_category");
    Route::post("category/delete/{id}","CategoryController@delete_category");
    Route::get("category/search/{search}/{token}/{pagination?}","CategoryController@search_category");

    Route::post("news/add","NewsController@add_news");
    Route::get("news/get-all/{token}/{pagination?}","NewsController@get_paginated_data");
    Route::post("news/update/{id}","NewsController@edit_data");
    Route::post("news/delete/{id}","NewsController@delete_news");
    Route::get("news/get-single/{id}","NewsController@get_single_data");
    Route::get("news/search/{search}/{token}/{pagination?}","NewsController@search_data");
} 
);

  