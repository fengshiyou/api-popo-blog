<?php

namespace App\Http\Controllers;



use Illuminate\Support\Facades\Validator;

trait AppValidataTrait
{
    //
    public function appValidata($rules, &$error = '', &$params = [], $messages = [])
    {
        $header = [];
        //约定头信息
        $conf = config('config.headers');
        foreach ($conf as $k => $v) {
            $header[$v] = request()->header($v);
        }
        $inputs = array_merge(request()->all(), $header);
        //创建验证
        $validator = Validator::make($inputs, $rules, $messages);
        //是否验证通过
        $flag = $validator->fails();
        if($flag){
            $error = $validator->errors()->first();
        }else{
            $params = $inputs;
        }
        return $flag;
    }
}
