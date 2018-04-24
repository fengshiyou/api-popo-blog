<?php

namespace App\Http\Controllers\blog;

use App\model\Catalog;
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
}
