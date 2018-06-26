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
     * @api {post} /api/user/register 01-用户注册
     * @apiDescription 用户注册
     * @apiGroup 01-user
     * @apiName register
     *
     *
     * @apiParam {String} acount 账号
     * @apiParam {String} passwd 密码
     * @apiParam {String} passwd_check 密码确认
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20003,
     * "detail": "账号已存在",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "uid": "6",
     * "token": "4MRhjarXvynrtkmS"
     * "acount": "fsy"
     * }
     */
    public function register()
    {
        $pro = [
            'acount' => 'required|between:2,30',
            'passwd' => 'required|between:2,18',
            'passwd_check' => 'required|between:2,18',
        ];
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
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
     * @api {post} /api/user/login 02-用户登陆
     * @apiDescription 用户登录
     * @apiGroup 01-user
     * @apiName login
     *
     *
     * @apiParam {String} acount 账号
     * @apiParam {String} passwd 密码
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20001,
     * "detail": "账号或密码错误",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "uid": "6",
     * "token": "4MRhjarXvynrtkmS"
     * "acount": "fsy"
     * }
     */
    public function login()
    {
        $pro = [
            'acount' => 'required|between:2,30',
            'passwd' => 'required|between:2,18'
        ];
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
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

    /**
     * @api {post} /api/user/register 03-退出登录
     * @apiDescription 退出登录
     * @apiGroup 01-user
     * @apiName logout
     *
     * @apiParam {Init} uid 用户ID
     *
     * @apiVersion 1.0.0
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * }
     */
    public function logout()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $redis = Redis::connection();
        $redis->del('TK_' . $p['uid']);
        return respSuc();
    }

    /**
     * @api {post} /api/user/getMemberInfoSimplify 04-用户信息(简版)
     * @apiDescription 用户信息(简版)-需要登陆验证
     * @apiGroup 01-user
     * @apiName getMemberInfoSimplify
     *
     *
     * @apiParam {Int} uid 用户ID
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20001,
     * "detail": "用户不存在",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "uid": "6",
     * "acount": "fsy"
     * }
     */
    public function getMemberInfoSimplify()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $info = Member::where('uid', $p['uid'])
            ->select('uid', 'acount')
            ->first();
        return respSuc($info);
    }

    /**
     * @api {post} /api/user/getMemberInfoDetail 05-用户详情
     * @apiDescription 用户详情-需要登陆验证
     * @apiGroup 01-user
     * @apiName getMemberInfoDetail
     *
     *
     * @apiParam {Int} uid 用户ID
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20001,
     * "detail": "用户不存在",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "uid": "6",
     * "header_welcome": "头部导航欢迎语" //头部导航欢迎语
     * "header_graph": "头部签名" //头部签名
     * "icon_url": "www.baidu.com" //头像链接
     * "motto": "座右铭" //座右铭
     * "link1": "www.baidu.com"
     * "link1_des": "超链接1描述" //超链接1描述
     * "link2": "www.baidu.com"
     * "link2_des": "超链接2描述" //超链接2描述
     * "link3": "www.baidu.com"
     * "link3_des": "超链接3描述" //超链接3描述
     * }
     */
    public function getMemberInfoDetail()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $info = Member::where('uid', $p['uid'])
            ->select(
                'member.uid',
                'member.acount',
                'member.header_welcome',
                'member.header_graph',
                'member.icon_url',
                'member.motto',
                'member.link1',
                'member.link1_des',
                'member.link2',
                'member.link2_des',
                'member.link3',
                'member.link3_des'
            )
            ->addSelect('power_role.power', 'power_role.web_url_power')
            ->leftJoin('power_role', 'member.power_role_id', '=', 'power_role.id')
            ->first();
        return respSuc($info);
    }

    /**
     * @api {post} /api/user/getMemberPower 06-获取登陆用户权限
     * @apiDescription 获取登陆用户权限-需要登陆验证
     * @apiGroup 01-user
     * @apiName getMemberPower
     *
     *
     * @apiHeader {Int} login_uid 用户ID
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20001,
     * "detail": "用户不存在",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "power": "6", //权限值
     * "web_url_power": "6", //前端路由权限值
     * }
     */
    public function getMemberPower()
    {
        $pro = array(
            'login_uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        $info = Member::where('uid', $p['login_uid'])
            ->select('power_role.power', 'power_role.web_url_power')
            ->leftJoin('power_role', 'member.power_role_id', '=', 'power_role.id')
            ->first();
        return respSuc($info);
    }

    /**
     * @api {post} /api/user/editUserInfo 07-修改用户详情
     * @apiDescription 修改用户详情-需要登陆验证
     * @apiGroup 01-user
     * @apiName editUserInfo
     *
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {String} [header_graph] 头部签名
     * @apiParam {String} [header_welcome] 头部导航欢迎语
     * @apiParam {String} [icon_url] 头像链接
     * @apiParam {String} [link1] 超链接1
     * @apiParam {String} [link1_des] 超链接1描述
     * @apiParam {String} [link2] 超链接2
     * @apiParam {String} [link2_des] 超链接2描述
     * @apiParam {String} [link3] 超链接3
     * @apiParam {String} [link3_des] 超链接3描述
     * @apiParam {String} [motto] 座右铭
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 20001,
     * "detail": "用户不存在",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {
     * "uid": "6",
     * "header_welcome": "头部导航欢迎语" //头部导航欢迎语
     * "header_graph": "头部签名" //头部签名
     * "icon_url": "www.baidu.com" //头像链接
     * "motto": "座右铭" //座右铭
     * "link1": "www.baidu.com"
     * "link1_des": "超链接1描述" //超链接1描述
     * "link2": "www.baidu.com"
     * "link2_des": "超链接2描述" //超链接2描述
     * "link3": "www.baidu.com"
     * "link3_des": "超链接3描述" //超链接3描述
     * }
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
     * @api {post} /api/user/getList 08-获取用户列表
     * @apiDescription 获取用户列表-需要登陆验证-需要权限验证
     * @apiGroup 01-user
     * @apiName getList
     *
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {String} [page_no] 第几页
     * @apiParam {String} [per_page] 每页数据量 (默认10 最大10)
     * @apiParam {String} [acount_search] 需要搜索的账号
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 1004,
     * "detail": "权限不足",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": [
     *     {
     *      "uid": "6",
     *      "acount": "1" //账号
     *      "created_at": "2018-08-08 10:10:10" //创建日期
     *      "enabled": "0" //1：启用 .：禁用
     *      "updated_at": "2018-08-08 10:10:10" //修改日期
     *      "power_role_id": "www.baidu.com"
     *     }
     * ......
     * ]
     */
    public function getList()
    {
        //页数
        $page_no = request()->get('page_no') ? request()->get('page_no') : 1;
        //每页数量
        $per_page = request()->get('per_page') > 0 && request()->get('per_page') <= 10 ? request()->get('per_page') : 10;
        $data = new Member();
        if (request()->get('acount_search')) {
            $data = $data->where('acount', 'like', "%" . request()->get('acount_search') . "%");
        }
        $total = $data->count();
        $data = $data->skip(($page_no - 1) * $per_page)
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

        return respSuc(['list' => $data, 'total' => $total, 'page_no' => $page_no]);
    }

    /**
     * @api {post} /api/user/resetPasswd 09-重置用户密码
     * @apiDescription 重置用户密码-需要登陆验证-需要权限验证
     * @apiGroup 01-user
     * @apiName resetPasswd
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {Int} uid 用户ID
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 1004,
     * "detail": "权限不足",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {}
     */
    public function resetPasswd()
    {
        $pro = array(
            'uid' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
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
     * @api {post} /api/user/setEnabled 10-设置用户启用禁用
     * @apiDescription 设置用户启用禁用-需要登陆验证-需要权限验证
     * @apiGroup 01-user
     * @apiName setEnabled
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {Int} uid 用户ID
     * @apiParam {Int} enabled 0：禁用 1：启用
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 1004,
     * "detail": "权限不足",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {}
     */
    public function setEnabled()
    {
        $pro = array(
            'uid' => 'required',
            'enabled' => 'required'
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
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
     * @api {post} /api/user/setPowerRole 11-设置用户权限角色
     * @apiDescription 设置用户权限角色-需要登陆验证-需要权限验证
     * @apiGroup 01-user
     * @apiName setPowerRole
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {Int} uid 用户ID
     * @apiParam {Int} power_role_id 权限角色ID
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 1004,
     * "detail": "权限不足",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data": {}
     */
    public function setPowerRole()
    {
        $pro = array(
            'uid' => 'required',
            'power_role_id' => 'required'
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
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
