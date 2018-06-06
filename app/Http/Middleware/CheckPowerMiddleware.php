<?php

namespace App\Http\Middleware;

use App\Model\Member;
use App\Model\PowerRole;
use App\Model\PowerUrl;
use Closure;
use Illuminate\Support\Facades\Redis;

class CheckPowerMiddleware extends BaseMiddleware
{
    public function match($request)
    {
        $uid = $request->get('login_uid');
        $member_power_info = Member::where('uid', $uid)
            ->select('power_role.power')
            ->leftJoin('power_role', 'member.power_role_id', '=', 'power_role.id')
            ->first();
        $member_power = $member_power_info ? $member_power_info->power : "";
        $url = $request->get('_url');
        $power_url_info = PowerUrl::where('url', $url)->first();

        if (!$power_url_info) {
            return 0;
        }
        $url_power = $power_url_info->power_mark;
        if (intval($member_power) & intval($url_power)) {
            return 0;
        }
        return respErr(1004);
    }
}
