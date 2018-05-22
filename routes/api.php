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

//blog相关API
Route::group(['namespace' => 'blog', 'prefix' => 'blog'], function () {
    Route::get('/getTags', 'BlogController@getTags');
    Route::get('/getCatalogList', 'CatalogController@getCatalogListHaveCount');
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
        //修改目录名称
        Route::post('/renameCatalog','CatalogController@rename');
        //新增目录
        Route::post('/newCatalog','CatalogController@newCatalog');
        //获取个人目录
        Route::post('/getMyCatalogList', 'CatalogController@getCatalogListHaveCount');
        //删除目录
        Route::post('/delMyCatalog', 'CatalogController@delCatalog');

    });
});

