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
    //保存博客
    public function blogSave()
    {
        //校验规则
        //@todo tags 是一个array
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $blog_service = new BlogServices();
        if (!$param['blog_id']) {
            $result = $blog_service->blogAdd($param);
        } else {
            $result = $blog_service->blogEdit($param);
        }
        return $result;
    }

    public function getTags()
    {
        return respSuc(Tag::get());
    }

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
            $blog_model = $blog_model->where('blog_tag.id', $tag_id)
                ->leftJoin('blog_tag', 'blog_tag.id', "=", "blog_list.id");
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
    public function requestTest(){
        return respErr(201,'nead_login');
    }
}
