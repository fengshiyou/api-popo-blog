<?php

namespace App\Http\Controllers\blog;

use App\model\BlogList;
use App\model\BlogTag;
use App\model\Catalog;
use App\model\Content;
use App\model\Tag;
use App\Services\BlogServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    /**
     * @api {post} /api/blog/save 01-保存博客
     * @apiDescription 保存博客-需要登陆验证
     * @apiGroup 02-blog
     * @apiName save
     *
     *
     * @apiHeader {Int} login_uid 用户ID
     * @apiParam {String} title 博客title
     * @apiParam {String} content 内容
     * @apiParam {String} tags 标签id字符串 例如:1,2,3,4
     * @apiParam {Int} [blog_id] 博客ID，存在则是修改博客不存在则是新增博客(修改时会验证博客所属)
     *
     * @apiVersion 1.0.0
     * @apiErrorExample {json} 错误返回值:
     * {
     * "code": 1003,
     * "detail": "该目录不属于你",
     * "data": ""
     * }
     * @apiSuccessExample {json} 正确返回值:
     * {
     * "code": 200,
     * "detail": "success",
     * "data":
     *     {
     *      "id": "1",//博客ID
     *     }
     */
    public function blogSave()
    {
        //校验规则
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $blog_service = new BlogServices();
        if (!array_key_exists('blog_id',$param)) {
            $result = $blog_service->blogAdd($param);
        } else {
            $result = $blog_service->blogEdit($param);
        }
        return $result;
    }
    /**
     * @api {get} /api/blog/getTags 05-获取博客标签
     * @apiDescription 修改博客时获取博客详情-需要登陆验证
     * @apiGroup 02-blog
     * @apiName getTags
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
     * "data":[
     *      {
     *           "color":"#FF6600",//标签颜色
     *           "id":"6",//标签ID
     *           "name":"0",//标签名称
     *      }
     * ......
     * ]
     */
    public function getTags()
    {
        return respSuc(Tag::get());
    }
    /**
     * @api {get} /api/blog/getList 02-博客列表
     * @apiDescription 博客列表
     * @apiGroup 02-blog
     * @apiName getList
     *
     *
     * @apiParam {Int} [page_no] 页数 默认1
     * @apiParam {Int} [per_page] 每页数量 默认10 最大10
     * @apiParam {Int} [tag_id] 如果有该ID则按tagid对博客进行搜索
     * @apiParam {Int} [catalog_id] 如果有该ID则按目录ID对博客进行搜索
     * @apiParam {Int} [uid] 如果有该ID则按用户ID对博客进行搜索
     * @apiParam {String} [order_by] 排序 默认按创建时间排序
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
     *                          "acount":"fsy",//博客所属账号
     *                          "catalog":"fsy|1|-1,test|6|1",//目录|目录ID|目录父ID
     *                          "catalog_id":"6",//当前所在目录ID
     *                          "comment_count":"0",//评论数量
     *                          "content_id":"1",//文章ID
     *                          "created_at":"2018-06-26 21:35:24",//创建时间
     *                          "id":1,//博客ID
     *                          "tags":"1,2,5",//标签id字符串 例如:1,2,3,4
     *                          "title":"test",//博客名称
     *                          "uid":"123123",//所属用户ID
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
        //页数
        $page_no = request()->get('page_no') ? request()->get('page_no') : 1;
        //每页数量
        $per_page = request()->get('per_page') > 0 && request()->get('per_page') <= 10 ? request()->get('per_page') : 10;
        //标签ID
        $tag_id = request()->get('tag_id') ? request()->get('tag_id') : '';
        //目录ID
        $catalog_id = request()->get('catalog_id') ? request()->get('catalog_id') : '';
        //用户ID
        $uid = request()->get('uid') ? request()->get('uid') : '';
        //排序
        $order_by = request()->get('order_by') ? request()->get('order_by') : 'created_at';
        $blog_model = new BlogList();
        if ($tag_id) {//按标签搜索
            $blog_model = $blog_model->where('blog_tag.tag_id', $tag_id)
                ->leftJoin('blog_tag', 'blog_tag.blog_id', "=", "blog_list.id");
        } elseif ($catalog_id) {//按目录搜索
            $blog_model = $blog_model->where('catalog_id', $catalog_id);
        }
        //查询用户
        if($uid){
            $blog_model = $blog_model->where('blog_list.uid', $uid);
        }
        //排序  因为getCatalog中也有排序  所以这个排序要放在前面
        if ($order_by) {
            $blog_model = $blog_model->orderBy('blog_list.' . $order_by, "desc");
        }
        $total =  $blog_model->count();
        //获取所属目录
        $blog_model = $blog_model->select('blog_list.*')->getCatalog();
        //获取博客内容
//        $blog_model = $blog_model->addSelect('content.content')->leftJoin('content','blog_list.content_id','=','content.id');
        //获取用户名
        $blog_model = $blog_model->addSelect('member.acount')->leftJoin('member','blog_list.uid','=','member.uid');
        //分页
        $blog_model = $blog_model->skip(($page_no - 1) * $per_page)
            ->take($per_page);
        //获取总数

        $blog_list = $blog_model->get();
        $return_data['list'] = $blog_list;
        $return_data['total'] = $total;
        $return_data['per_page'] = intval($per_page);
        $return_data['page_no'] = intval($page_no);
        return respSuc($return_data);
    }
    /**
     * @api {post} /api/blog/getContent 03-博客的文章详情
     * @apiDescription 博客的文章详情
     * @apiGroup 02-blog
     * @apiName getContent
     *
     *
     * @apiParam {Int} content_id 文章ID
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
     *           "catalog_id":"6",//当前所在目录ID
     *           "comment_count":"0",//评论数量
     *           "content_id":"1",//文章ID
     *           "content":"testxxxxxxxxxxxx",//文章内容
     *           "created_at":"2018-06-26 21:35:24",//创建时间
     *           "id":1,//博客ID
     *           "tags":"1,2,5",//标签id字符串 例如:1,2,3,4
     *           "title":"test",//博客名称
     *           "uid":"123123",//所属用户ID
     *           "updated_at":"2018-06-26 21:39:06"//更新时间
     *      }
     */
    public function getContent(){
        $rules = [
            'content_id' => 'required'
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $content = Content::leftJoin('blog_list','content.id','=','blog_list.content_id')->where('blog_list.id',$param['content_id'])->first();
        return respSuc($content);
    }
    /**
     * @api {post} /api/blog/getEditContent 04-修改博客时获取博客详情
     * @apiDescription 修改博客时获取博客详情-需要登陆验证
     * @apiGroup 02-blog
     * @apiName getEditContent
     *
     *
     * @apiParam {Int} blog_id 博客ID
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
     *           "catalog":"fsy|1|-1,test|6|1",//目录|目录ID|目录父ID
     *           "catalog_id":"6",//当前所在目录ID
     *           "comment_count":"0",//评论数量
     *           "content_id":"1",//文章ID
     *           "content":"testaaaaaaa",//文章内容
     *           "created_at":"2018-06-26 21:35:24",//创建时间
     *           "id":1,//博客ID
     *           "tags":"1,2,5",//标签id字符串 例如:1,2,3,4
     *           "title":"test",//博客名称
     *           "uid":"123123",//所属用户ID
     *           "updated_at":"2018-06-26 21:39:06"//更新时间
     *      }
     */
    public function getEditContent(){
        $rules = [
            'blog_id' => 'required'
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $content = BlogList::leftJoin('content','content.id','=','blog_list.content_id')
            ->getCatalog()
            ->addSelect('blog_list.*','content.content')
            ->where('blog_list.id',$param['blog_id'])
            ->first();
        if(!$content){
            return respErr(1002);
        }
        //验证修改者是否是创建者
        if($content->uid != $param['login_uid']){
            return respErr(1001);
        }
        return respSuc($content);
    }
    public function requestTest(){
        return respErr(201,'nead_login');
    }
}
