<?php

namespace App\Http\Controllers\Reply;

use App\Model\Reply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * 回复评论
 * Class ReplyController
 * @package App\Http\Controllers\Reply
 */
class ReplyController extends Controller
{
    public function add()
    {
        //校验规则
        $rules = [
            'comment_id' => 'required',//评论ID
            'content' => 'required',//回复内容
//            'to_uid' => 'required',//回复目标的uid
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        //@todo 回复权限？
        $reply = new Reply();
        $reply->uid = $param['login_uid'];
        $reply->content = $param['content'];
        $reply->comment_id = $param['comment_id'];
        $reply->to_uid = array_key_exists('to_uid', $param) ? $param['to_uid'] : "";
        $reply->created_at = now();
        $reply->updated_at = now();

        $save = $reply->save();
        $data = Reply::where('id', $reply->id)
            ->select('reply.*')
            ->addSelect('member1.acount')
            ->addSelect('member2.acount as to_acount')
            ->leftJoin('member as member1', 'reply.uid', '=', 'member1.uid')
            ->leftJoin('member as member2', 'reply.to_uid', '=', 'member2.uid')
            ->first();
        if ($save) {
            return respSuc($data);
        } else {
            return respErr(10000);
        }
    }

    /**
     * 获取回复列表
     */
    public function getList()
    {
        //校验规则
        $rules = [
            'comment_id' => 'required',
            'page_no' => 'required'
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        //每页数量
        $per_page = 10;
        $list = Reply::where('comment_id', $param['comment_id'])
            ->select('reply.*')
            ->addSelect('member1.acount')
            ->addSelect('member2.acount as to_acount')
            ->leftJoin('member as member1', 'reply.uid', '=', 'member1.uid')
            ->leftJoin('member as member2', 'reply.to_uid', '=', 'member2.uid')
            ->skip(($param['page_no'] - 1) * $per_page)
            ->take($per_page)
            ->orderBy('id', 'asc')
            ->get();
        $total = Reply::where('comment_id', $param['comment_id'])
            ->count();
        $return_data['list'] = $list;
        $return_data['page_no'] = intval($param['page_no']);
        $return_data['total'] = $total;
        $return_data['per_page'] = $per_page;

        return respSuc($return_data);
    }
}
