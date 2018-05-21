<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/4/18
 * Time: 10:30
 */

namespace App\Services;

use App\model\BlogList;
use App\model\BlogTag;
use App\model\content;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class BlogServices
{
    public function blogAdd($info)
    {
        $blog_tag = [];
        //内容
        $content_model = new Content();
        //博客
        $blog_model = new BlogList();
        //开启事务
        DB::beginTransaction();
        //保存内容
        $content_id = $content_model->insertGetId(['content' => $info['content'], 'updated_at' => date("Y-m-d H:i:s"), 'created_at' => date("Y-m-d H:i:s")]);
        //标签处理
        $tags = $info['tags'] ? implode($info['tags'], ',') : "";

        //博客数据
        $blog_data = [
            'title' => $info['title'],
            'uid' => $info['login_uid'],
            'content_id' => $content_id,
            'catalog_id' => $info['catalog_id'],
            'tags' => $tags,
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s"),
        ];
        //博客列表保存
        $blog_id = $blog_model->insertGetId($blog_data);
        //标签处理
        if ($info['tags']) {
            foreach ($info['tags'] as $tag_id) {
                $blog_tag[] = [
                    'blog_id' => $blog_id,
                    'tag_id' => $tag_id,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'created_at' => date("Y-m-d H:i:s"),
                ];
            }
        }

        //博客标签保存
        $blog_tag_insert = BlogTag::insert($blog_tag);
        if ($content_id && $blog_id && $blog_tag_insert) {
            DB::commit();
            return respSuc();
        } else {
            DB::rollback();
            return respErr(10000);
        }
    }

    public function blogEdit($info)
    {
        //标签处理
        $blog_tag_model = new BlogTag();
        $tags = $info['tags'] ? implode($info['tags'], ',') : "";
        //博客信息
        $blog_model = new BlogList();
        $blog_info = $blog_model->where("id", $info['blog_id'])->first();
        $blog_info->title = $info['title'];
        $blog_info->catalog_id = $info['catalog_id'];
        $blog_info->tags = $tags;
        $blog_info->updated_at = date("Y-m-d H:i:m");
        //验证博客作者
        if ($blog_info->uid != $info['login_uid']) {
            return respErr(1001);
        }
        //博客内容
        $content_model = new Content();
        $content_info = $content_model->where("id", $blog_info->content_id)->first();
        $content_info->content = $info['content'];
        $content_info->updated_at = date("Y-m-d H:i:m");
        //新增标签
        $blog_tag = [];
        foreach ($info['tags'] as $tag_id) {
            $blog_tag[] = [
                'blog_id' => $info['blog_id'],
                'tag_id' => $tag_id,
                'updated_at' => date("Y-m-d H:i:s"),
                'created_at' => date("Y-m-d H:i:s"),
            ];
        }
        //开启事务
        DB::beginTransaction();
        try {
            //删除老标签
            $blog_tag_model->where('blog_id', $info['blog_id'])->delete();
            //博客标签保存
            $blog_tag_model->insert($blog_tag);
            //博客信息保存
            $blog_info->save();
            //博客内容保存
            $content_info->save();
            DB::commit();
            return respSuc();
        } catch (Exception $e) {
            DB::rollback();//事务回滚
            echo $e->getMessage();
            echo $e->getCode();
            return respErr(10000);
        }
    }
}