<?php

namespace App\Http\Controllers\tag;

use App\model\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    /**
     * 新增标签
     */
    public function add()
    {

    }

    /**
     * 删除标签
     */
    public function del()
    {
        $pro = array(
            'id' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        Tag::where('id', $p['id'])->delete();
        return respSuc();
    }

    /**
     * 编辑标签
     */
    public function edit()
    {
        $pro = array(
            'name' => 'required',
            'color' => 'required',
        );
        if ($this->appValidata($pro, $error, $p)) {
            return respErr(50000, $error);
        }
        if (array_key_exists('id',$p)) {//修改
            Tag::where('id', $p['id'])->update(['name' => $p['name'], 'color' => $p['color']]);
        } else {//新增
            $tag = new Tag();
            $tag->name = $p['name'];
            $tag->color = $p['color'];
            $tag->save();
        }
        return respSuc();
    }
}
