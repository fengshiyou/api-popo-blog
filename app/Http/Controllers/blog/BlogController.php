<?php

namespace App\Http\Controllers\blog;

use App\model\test;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    //保存博客
    public function blogSave()
    {
        $rules = [
            'title' => 'required'
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }

        $test_model = new test();
        $test_model->test = 123123123;
        $test_model->save();
        return respSuc();
    }
}
