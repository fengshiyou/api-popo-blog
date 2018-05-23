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
     * 获取目录列表   包含文章数
     * @return \Illuminate\Http\JsonResponse
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
        //@todo 根目录不允许修改名称
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
        $catalog_model = new Catalog();
        $catalog_model->lef = $catalog_info->rig;
        $catalog_model->rig = $catalog_info->rig + 1;
        $catalog_model->parent_id = $param['catalog_id'];
        $catalog_model->catalog_name = $param['catalog_name'];
        $catalog_model->uid = $param['login_uid'];
        DB::beginTransaction();
        $update_lef = $catalog_model->where('lef', '>=', $catalog_info->rig)->increment('lef', 2);
        $update_rig = $catalog_model->where('rig', '>=', $catalog_info->rig)->increment('rig', 2);
        $new_catalog = $catalog_model->save();

        if ($update_lef && $update_rig && $new_catalog) {
            DB::commit();
            return respSuc();
        } else {
            DB::rollback();
            return respErr(10000);
        }
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
