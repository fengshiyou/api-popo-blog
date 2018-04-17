<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/4/17
 * Time: 10:51
 */

//@todo 整理到博客中去   自定义函数的创建  具体方法在笔记中
//返回 json
if (!function_exists('respJson')) {
    function respJson($result)
    {
        //添加header  允许任何域名访问
        header("Access-Control-Allow-Origin:*");
        // JSON_UNESCAPED_UNICODE 这个参数可以json不转译unicode值
        // 如果不加默认是输出如 {"hello":"\u4e16\u754c"}
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
//错误返回
if (!function_exists('respErr')) {
    function respErr($code = 500, $msg = '')
    {
        $msg = config('errorCode.' . $code) . ":" . $msg;
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => ''
        ];
        return respJson($result);
    }
}
//成功返回
if (!function_exists('respSuc')) {
    function respSuc($data = '', $msg = "success")
    {
        $result = [
            'code' => 200,
            'msg' => $msg,
            'data' => $data
        ];
        return respJson($result);
    }
}