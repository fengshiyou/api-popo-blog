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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'test'], function () {
    Route::get('/test', function () {
        $zzz = respSuc('adsf');
        return $zzz;
    });
});
//blog相关API
Route::group(['namespace' => 'blog', 'prefix' => 'blog','middleware'=>'test'], function () {
    Route::get('/getTags', 'BlogController@getTags');
    Route::get('/getCatalogList', 'CatalogController@getCatalogList');
    Route::get('/getList', 'BlogController@getList');
    Route::post('/getContent', 'BlogController@getContent');
});
Route::group(['namespace'=>'user','prefix'=>'user'],function (){
    Route::post('/login','UserController@login');
    Route::post('/register','UserController@register');
});
//需要登陆验证
Route::group(['middleware'=>'checklogin'],function (){
    //博客相关
    Route::group(['namespace' => 'blog', 'prefix' => 'blog'], function () {
        //获取博客列表
        Route::get('/myBlog', 'BlogController@getList');
        //保存博客内容
        Route::post('/save', 'BlogController@blogSave');
        //修改时 获取博客内容
        Route::post('/getEditContent', 'BlogController@getEditContent');

    });
});
//@todo 需要登陆验证的内容
Route::get('/request/test', 'blog\BlogController@requestTest');

