<?php

namespace App\Http\Middleware;

use App\model\Log;
use App\Model\Member;
use App\Model\PowerRole;
use App\Model\PowerUrl;
use Closure;
use Illuminate\Support\Facades\Redis;

class LogMiddleware extends BaseMiddleware
{
    public function match($request)
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        $uid = request()->get('login_uid');
        $member_info = '';
        if ($uid) {
            $member_info = Member::where('uid', $uid)->first();
        }
        $log = new Log();
        $log->client_ip = $realip;
        $log->acount = $member_info ? $member_info->acount : '';
        $log->action = request()->get('_url');
        $log->params = json_encode(request()->all());
        $log->created_at = now();
        $log->save();
        return 0;
    }
}
