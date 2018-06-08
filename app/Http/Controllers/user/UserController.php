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
        $user_model->passwd = md5($p['passwd'] . $solt . $p['acount'] . $now);
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
        if ($user_info->enabled == 0) {
            return respErr(10);
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
        //@todo 权限验证
        $redis = Redis::connection();
        $redis->del('TK_' . $p['uid']);
        return respSuc();
    }

    /**
     * 获取用户信息简版
     */
    public function getMemberInfoSimplify()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $info = Member::where('uid', $p['uid'])
            ->select('uid', 'acount')
            ->first();
        return respSuc($info);
    }

    /**
     * 获取用户信息详情
     */
    public function getMemberInfoDetail()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $info = Member::where('uid', $p['uid'])
            ->select(
                'uid',
                'acount',
                'header_welcome',
                'header_graph',
                'icon_url',
                'motto',
                'link1',
                'link1_des',
                'link2',
                'link2_des',
                'link3',
                'link3_des'
            )
            ->first();
        return respSuc($info);
    }

    /**
     * 修改用户详情
     */
    public function editUserInfo()
    {
        $input = request()->all();
        $user = Member::find($input['login_uid']);
        $user->header_graph = array_key_exists('header_graph', $input) ? $input['header_graph'] : '';
        $user->header_welcome = array_key_exists('header_welcome', $input) ? $input['header_welcome'] : '';
        $user->icon_url = array_key_exists('icon_url', $input) ? $input['icon_url'] : '';
        $user->link1 = array_key_exists('link1', $input) ? $input['link1'] : '';
        $user->link1_des = array_key_exists('link1_des', $input) ? $input['link1_des'] : '';
        $user->link2 = array_key_exists('link2', $input) ? $input['link2'] : '';
        $user->link2_des = array_key_exists('link2_des', $input) ? $input['link2_des'] : '';
        $user->link3 = array_key_exists('link3', $input) ? $input['link3'] : '';
        $user->link3_des = array_key_exists('link3_des', $input) ? $input['link3_des'] : '';
        $user->motto = array_key_exists('motto', $input) ? $input['motto'] : '';
        $save = $user->save();
        if ($save) {
            return respSuc($user);
        } else {
            return respErr(10000);
        }
    }

    /**
     * 获取用户列表
     */
    public function getList()
    {
        //页数
        $page_no = request()->get('page_no') ? request()->get('page_no') : 1;
        //每页数量
        $per_page = request()->get('per_page') > 0 && request()->get('per_page') <= 10 ? request()->get('per_page') : 10;
        $member = new Member();
        $total = $member->count();
        $data = $member->skip(($page_no - 1) * $per_page)
            ->select(
                'uid',
                'acount',
                'created_at',
                'enabled',
                'updated_at',
                'power_role_id'
            )
            ->take($per_page)
            ->orderBy('uid', 'desc')
            ->get();

        return respSuc(['list' => $data, 'total' => $total]);
    }

    /**
     * 重置密码
     */
    public function resetPasswd()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $member = new Member();
        $user_info = $member->where('uid', $p['uid'])->first();
        $passwd = md5("123456" . $user_info->solt . $user_info->acount . $user_info->created_at);
        $user_info->passwd = $passwd;
        $re = $user_info->save();
        if ($re) {
            return respSuc();
        } else {
            return respErr(10000);
        }
    }

    /**
     * 设置用户启用禁用
     */
    public function setEnabled()
    {
        $pro = array(
            'uid' => 'required',
            'enabled' => 'required'
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $member_info = Member::where('uid', $p['uid'])->first();
        $member_info->enabled = $p['enabled'] ? 1 : 0;
        $save = $member_info->save();
        if ($save) {
            return respSuc();
        } else {
            return respErr(10000);
        }
    }

    /**
     * 设置用户权限角色
     */
    public function setPowerRole()
    {
        $pro = array(
            'uid' => 'required',
            'power_role_id' => 'required'
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(5000, $error);
        }
        $member_info = Member::where('uid', $p['uid'])->first();
        $member_info->power_role_id = $p['power_role_id'];
        $save = $member_info->save();
        if ($save) {
            return respSuc();
        } else {
            return respErr(10000);
        }
    }
}
