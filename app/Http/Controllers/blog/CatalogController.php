<?php

namespace App\Http\Controllers\blog;

use App\model\Catalog;
use App\Services\CatalogServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * @api {get} /api/blog/getMyCatalogList 01-目录列表 带文章数
     * @apiDescription 目录列表 带文章数
     * @apiGroup 03-catalog
     * @apiName getCatalogList
     *
     *
     * @apiHeader {Int} [uid] 用户ID，默认登陆者ID
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
     *          "catalog_name":"fsy",//目录名称
     *          "count":12,//该目录下包含文章数
     *          "created_at":"2018-06-26 11:29:43",//创建时间
     *          "updated_at":"2018-06-26 11:29:43",//更新时间
     *          "id":1,//目录ID
     *          "lef":"1",//左值
     *          "parent_id":"-1",//父目录ID -1代表没有父目录(根目录)
     *          "rig":"4",//右值
     *          "uid":"1",//所属用户ID
     *          "next":[//下级目录信息
     *                  ......
     *                ]
     *      }
     */
    public function getCatalogListHaveCount()
    {
        //uid 查询的用户名    login_uid 登陆账户的uid
        $uid = request()->get('uid') ? request()->get('uid') : request()->get('login_uid');
        if (!$uid) {
            return respErr(50000);
        }
        $catalog_list = Catalog::getCatalogListHaveCount($uid)->get();
        if ($catalog_list) {
            $catalog_list = $catalog_list->toArray();
        }
        $catalog_list = $this->formatCatalogList($catalog_list);
        return respSuc($catalog_list);
    }

    public function getCatalogList()
    {
        //uid 查询的用户名    login_uid 登陆账户的uid
        $uid = request()->get('uid') ? request()->get('uid') : request()->get('login_uid');
        if (!$uid) {
            return respErr(50000);
        }
        $catalog_list = Catalog::where('uid', $uid)->get();
        if ($catalog_list) {
            $catalog_list = $catalog_list->toArray();
        }
        $catalog_list = $this->formatCatalogList($catalog_list);
        return respSuc($catalog_list);
    }

    /**
     * 对catalog_list 进行排序
     */
    protected function formatCatalogList($catalog_list)
    {
        $return_list = [];
        $temp_list = [];
        foreach ($catalog_list as $v) {
            $temp_list[$v['id']] = $v;
            $temp_list[$v['id']]['next'] = [];
        }
        foreach ($temp_list as $v) {
            if (array_key_exists($v['parent_id'], $temp_list)) {
                $temp_list[$v['parent_id']]['next'][] = &$temp_list[$v['id']];
            } else {
                $return_list[] = &$temp_list[$v['id']];
            }
        }
        return $return_list;
    }

    /**
     * 重命名目录名称
     */
    public function rename()
    {
        //校验规则
        $rules = [
            'catalog_id' => 'required',
            'new_name' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $catalog_info = Catalog::where('id', $param['catalog_id'])->first();
        if (!$catalog_info) {
            return respErr(30001);
        }
        if ($catalog_info->uid != $param['login_uid']) {
            return respErr(1003);
        }
        if($catalog_info->parent_id == -1){
            return respErr(30003);
        }
        $catalog_info->catalog_name = $param['new_name'];
        $catalog_info->updated_at = now();
        $catalog_info->save();
        return respSuc();
    }

    /**
     * 新增子目录
     */
    public function newCatalog()
    {
        //校验规则
        $rules = [
            'catalog_id' => 'required',
            'catalog_name' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $catalog_info = Catalog::where('id', $param['catalog_id'])->first();
        if (!$catalog_info) {
            return respErr(30001);
        }
        if ($catalog_info->uid != $param['login_uid']) {
            return respErr(1003);
        }
        $catalog_service = new CatalogServices();

        return $catalog_service->addCatalog($catalog_info,$param['catalog_name']);
    }
    /**
     * 删除目录
     */
    public function delCatalog(){
        //转移文章

        //校验规则
        $rules = [
            'catalog_id' => 'required',
        ];
        if ($this->appValidata($rules, $error, $param)) {
            return respErr(50000, $error);
        }
        $catalog_info = Catalog::where('id', $param['catalog_id'])->first();
        if (!$catalog_info) {
            return respErr(30001);
        }
        if ($catalog_info->uid != $param['login_uid']) {
            return respErr(1003);
        }
        if($catalog_info->parent_id == -1){
            return respErr(30002);
        }
        $catalog_service = new CatalogServices();
        return $catalog_service->delCatalog($catalog_info);
    }

}
