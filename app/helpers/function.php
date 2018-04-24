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
if (!function_exists('dv_arr')) {
    function dv_arr($vars, $label = '', $return = false)
    {
        if (ini_get('html_errors')) {
            $content = "<pre>\n";
            if ($label != '') {
                $content .= "<strong>{$label} :</strong>\n";
            }
            $content .= htmlspecialchars(print_r($vars, true));
            $content .= "\n</pre>\n";
        } else {
            $content = $label . " :\n" . print_r($vars, true);
        }
        if ($return) {
            return $content;
        }
        echo $content;
        return null;
    }
}
if (!function_exists('dv')) {
    function dv($value, $tag = 'dv')
    {
        if (config('app_debug', true)) {
            if (is_array($value)) {
                echo $tag ? $tag . ':<br>' : '';
                dv_arr($value);
                echo "----------------------<br>";
            } else {
                if ($tag) {
                    echo '<font color=blue>' . $tag . '=' . $value . '</font><br>';
                } else {
                    echo '<font color=blue>' . $value . '</font><br>';
                }
            }
        }
    }
}
if (!function_exists('dd')) {
    function dd($value)
    {
        dv($value);
        die();
    }
}
if (!function_exists('config')) {
    function config($name, $default = '')
    {
        //      测试环境获取配置
        $arr = explode('.', $name);
        $path = __DIR__;
        if (strstr(__DIR__, "vendor")) {
            $path = $path . '/../../..';
        }
        $path = $path . '/../config/' . $arr[0] . '.php';
        if (!file_exists($path)) {
            return $default;
        }
        $conf = require $path;// 此处不能用用require_once
        if (sizeof($arr) > 1) {
            for ($i = 1; $i < sizeof($arr); $i++) {
                if (array_key_exists($arr[$i], $conf)) {
                    $conf = $conf[$arr[$i]];
                } else {
                    return $default;
                }
            }
        }
        return $conf;
    }

}