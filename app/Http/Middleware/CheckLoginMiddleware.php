<?php

namespace App\Http\Middleware;

use App\Model\Member;
use Closure;
use Illuminate\Support\Facades\Redis;

class CheckLoginMiddleware extends BaseMiddleware
{
    public function match($request)
    {
        $uid = $request->header('loginUid');
        $token = $request->header('token');

        if (!$token) {
            return respErr(1);
        }
        if (!$uid) {
            return respErr(1);
        }
        $redis = Redis::connection();
        $redis_token = $redis->get('TK_' . $uid);
        if ($redis_token == $token) {
//            $mid_params['token'] = $token;
            $mid_params['login_uid'] = $uid;
            $request->merge($mid_params);
            $user_info = Member::where('uid', $uid)->first();
            if ($user_info->enabled == 0) {
                return respErr(10);
            }
            return 0;
        } else {
            return respErr(1);
        }
    }
}
