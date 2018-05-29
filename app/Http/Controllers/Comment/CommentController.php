<?php

namespace App\Http\Controllers\Comment;

use App\model\BlogList;
use App\Model\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    /**
     * 留言
     * @comment_type blog:对博客留言 user:对用户留言
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
     * 获取评论列表
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
