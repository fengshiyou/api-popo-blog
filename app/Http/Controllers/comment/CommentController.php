<?php

namespace App\Http\Controllers\Comment;

use App\model\BlogList;
use App\Model\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    /**
     * @api {post} /api/comment/add 01-添加评论
     * @apiDescription 添加评论-需要登陆验证
     * @apiGroup 04-comment
     * @apiName getMyCatalogList
     *
     *
     * @apiHeader {Int} login_uid 用户ID
     *
     * @apiParam {String} comment_type blog:对博客留言 user:对用户留言
     * @apiParam {Int} id blog_id
     * @apiParam {String} content 评论内容
     *
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 50000,
     * "detail": "必填字段缺少",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data":
     *      {
     *          "id":"1",//评论ID
     *          "floor":"1",//该评论所在楼层
     *      }
     */
    public function add()
    {
        //校验规则
        $rules = [
            'comment_type' => 'required',
            'id' => 'required',
            'content' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $comment = new Comment();
        $comment->uid = $param['login_uid'];
        $comment->type = $param['comment_type'];
        $comment->type_id = $param['id'];
        $comment->content = $param['content'];
        $comment->created_at = now();
        $comment->updated_at = now();
        //如果是文章 更新文章评论数
        if($param['comment_type'] == 'blog'){
            BlogList::where('id', $param['id'])->increment('comment_count',1);
        }
        $save = $comment->save();
        $floor = Comment::where('type', $param['comment_type'])
            ->where('type_id', $param['id'])
            ->where('id','<=',$comment->id)
            ->count();
        if ($save) {
            return respSuc(['id'=>$comment->id,'floor'=>$floor]);
        } else {
            return respErr(10000);
        }
    }

    /**
     * @api {get} /api/comment/getList 02-获取评论列表
     * @apiDescription 获取评论列表
     * @apiGroup 04-comment
     * @apiName getList
     *
     *
     * @apiParam {Int} [page_no] 页数 默认1
     * @apiParam {String} comment_type blog:对博客留言 user:对用户留言
     * @apiParam {Int} id blog_id
     *
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 500,
     * "detail": "其他错误",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data":
     *      {
     *          "page_no":1,//当前页数
     *          "list":[
     *                      {
     *                          "acount":"fsy",//评论人账号
     *                          "id":"1",//评论ID
     *                          "uid":"1",//评论人uid
     *                          "type":"0",//blog:对博客留言 user:对用户留言
     *                          "type_id":"1",//uid或者博客id
     *                          "created_at":"2018-06-26 21:35:24",//创建时间
     *                          "content":"123123123123阿迪斯发地方",//评论内容
     *                          "reply_count":"回复数量",//评论内容
     *                          "updated_at":"2018-06-26 21:39:06"//更新时间
     *                      }
     *                  ......
     *                ],
     *          "per_page":10,
     *          "total":400,
     *      }
     */
    public function getList()
    {
        //校验规则
        $rules = [
            'comment_type' => 'required',
            'id' => 'required',
            'page_no' => 'required'
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        //每页数量
        $per_page = 10;
        $list = Comment::where('type', $param['comment_type'])
            ->where('type_id', $param['id'])
            ->select('comment.*')
            ->addSelect('member.acount')
            ->leftJoin('member', 'comment.uid', '=', 'member.uid')
            ->skip(($param['page_no'] - 1) * $per_page)
            ->take($per_page)
            ->orderBy('id','asc')
            ->get();
        $total = Comment::where('type', $param['comment_type'])
            ->where('type_id', $param['id'])
            ->count();
        $return_data['list'] = $list;
        $return_data['page_no'] = intval($param['page_no']);
        $return_data['total'] = $total;
        $return_data['per_page'] = $per_page;

        return respSuc($return_data);
    }
    /**
     * 获取最后一条评论
     */
    public function getLast(){
        //校验规则
        $rules = [
            'comment_type' => 'required',
            'id' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $comment = Comment::where('type', $param['comment_type'])
            ->where('type_id', $param['id'])
            ->select('comment.*')
            ->addSelect('member.acount')
            ->leftJoin('member', 'comment.uid', '=', 'member.uid')
            ->orderBy('id','desc')
            ->first();

        return respSuc($comment);
    }
}
