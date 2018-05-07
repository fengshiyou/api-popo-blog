<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckLoginMiddleware extends BaseMiddleware
{
    public function match($request)
    {
        $uid = $request->get('uid');
        $token = $request->get('token');

        if (!$token) {
            return respErr(1, '缺少token');
        }
        if (!$uid) {
            return respErr(1, '缺少user_id');
        }
        $redis = Redis::connection();
        $redis_token = $redis->get('TK_' . $uid);
        if ($redis_token == $token) {
            $mid_params['token'] = $token;
            $mid_params['uid'] = $uid;
            $request->merge($mid_params);
            return 0;
        } else {
            return respErr(1, "请先登录");
        }
    }
}
