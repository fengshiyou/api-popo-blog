<?php

namespace App\Http\Controllers\user;

use App\Model\Member;
use App\Services\CatalogServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    /**
     * 用户注册
     */
    public function register()
    {
        $pro = [
            'acount' => 'required|between:2,30',
            'passwd' => 'required|between:2,18',
            'passwd_check' => 'required|between:2,18',
        ];
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        if ($p['passwd'] != $p['passwd_check']) {
            return respErr(20002);
        }
        $user_info = Member::where('acount', $p['acount'])->first();
        if ($user_info) {
            return respErr(20003);
        }
        $now = now();
        $solt = get_rand_char(4);
        $user_model = new Member();
        $user_model->acount = $p['acount'];
        $user_model->solt = $solt;
        $user_model->passwd = md5($p['passwd'] . $solt . $p['acount']. $now);
        $user_model->created_at = $now;
        $user_model->updated_at = $now;
        $user_model->save();
        //初始化用户目录
        $catalog_service = new CatalogServices();
        $catalog_service->initCatalog($user_model);
        return $this->login();
    }

    /**
     * 用户登陆
     */
    public function login()
    {
        $pro = [
            'acount' => 'required|between:2,30',
            'passwd' => 'required|between:2,18'
        ];
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        //获取用户信息
        $user_info = Member::where('acount', $p['acount'])->first();
        if (!$user_info) {
            return respErr(20001);
        }
        //密码 + solt + 用户名 + 创建时间 = 最终密码
        $passwd = md5($p['passwd'] . $user_info->solt . $user_info->acount . $user_info->created_at);
        //校验密码
        if ($passwd != $user_info['passwd']) {
            return respErr(20001);
        }
        //链接redis
        $redis = Redis::connection();
        //生成token
        $token = get_rand_char(16);
        $redis->set('TK_' . $user_info->uid, $token);
        //返回数据
        $return_data = [];
        $return_data['uid'] = $user_info['uid'];
        $return_data['token'] = $token;
        $return_data['user_name'] = $user_info['user_name'];
        $return_data['acount'] = $user_info['acount'];
        return respSuc($return_data);
    }

    /*
     * 登出
     */
    public function logout()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $redis = Redis::connection();
        $redis->del('TK_' . $p['uid']);
        return respSuc();
    }
}
