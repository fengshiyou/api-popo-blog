<?php

namespace App\Http\Controllers\blog;

use App\model\Catalog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CatalogController extends Controller
{
    /**
     * 获取目录列表   包含文章数
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCatalogListHaveCount()
    {
        //uid 查询的用户名    login_uid 登陆账户的uid
        $uid = request()->get('uid') ? request()->get('uid') : request()->get('login_uid');
        if(!$uid){
            return respErr(50000);
        }
        $catalog_list = Catalog::getCatalogListHaveCount($uid)->get();
        if($catalog_list){
            $catalog_list = $catalog_list->toArray();
        }
        $catalog_list = $this->formatCatalogList($catalog_list);
        return respSuc($catalog_list);
    }
    public function getCatalogList(){
        //uid 查询的用户名    login_uid 登陆账户的uid
        $uid = request()->get('uid') ? request()->get('uid') : request()->get('login_uid');
        $uid = 1;
        if(!$uid){
            return respErr(50000);
        }
        $catalog_list = Catalog::where('uid',$uid)->get();
        if($catalog_list){
            $catalog_list = $catalog_list->toArray();
        }
        $catalog_list = $this->formatCatalogList($catalog_list);
        return respSuc($catalog_list);
    }
    /**
     * 对catalog_list 进行排序
     */
    protected function formatCatalogList($catalog_list){
        $return_list = [];
        $temp_list = [];
        foreach ($catalog_list as $v){
            $temp_list[$v['id']] = $v;
            $temp_list[$v['id']]['next'] = [];
        }
        foreach ($temp_list as $v){
            if(array_key_exists($v['parend_id'],$temp_list)){
                $temp_list[$v['parend_id']]['next'][] = &$temp_list[$v['id']];
            }else{
                $return_list[] = &$temp_list[$v['id']];
            }
        }
        return $return_list;
    }
    /**
     * 重命名目录名称
     */
    public function rename(){
        //校验规则
        $rules = [
            'catalog_id' => 'required',
            'new_name' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
    }
}
