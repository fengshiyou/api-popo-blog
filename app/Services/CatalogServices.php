<?php
/**
 * Created by PhpStorm.
 * User: feng
 * Date: 2018/4/18
 * Time: 10:30
 */

namespace App\Services;


use App\model\BlogList;
use App\model\Catalog;
use App\Model\Member;
use Illuminate\Support\Facades\DB;

class CatalogServices
{
    /**
     * 初始化用户目录
     * 用用户名做根目录
     */
    public function initCatalog(Member $user)
    {
        $catalog = new Catalog();
        $catalog->uid = $user->id;
        $catalog->catalog_name = $user->acount;
        $catalog->lef = 1;
        $catalog->rig = 2;
        $catalog->parent_id = -1;
        $catalog->updated_at = now();
        $catalog->created_at = now();
        $catalog->save();
    }

    /**
     * 删除目录
     */
    public function delCatalog(Catalog $catalog)
    {
        //要删除目录及其子目录所占有的所有空间
        $length = $catalog->rig - $catalog->lef + 1;

        DB::beginTransaction();
        $move = $this->moveCatalogBlog($catalog->id, $catalog->parent_id);
        $del = Catalog::where('lef', '>=', $catalog->lef)->where('rig', '<=', $catalog->rig)->delete();
        $update_rig = Catalog::where('rig', '>', $catalog->rig)->increment('rig', -$length);
        $update_lef = Catalog::where('lef', '>', $catalog->rig)->increment('lef', -$length);

        if ($del && $update_rig && $update_lef && $move) {
            DB::commit();
            return respSuc();
        } else {
            DB::rollback();
            return respErr(10000);
        }
    }

    /**
     * 新增子目录
     * @param Catalog $catalog 新增目录的父目录
     * @param $name 新增目录的名字
     */
    public function addCatalog(Catalog $catalog, $name)
    {
        $catalog_model = new Catalog();
        $catalog_model->lef = $catalog->rig;
        $catalog_model->rig = $catalog->rig + 1;
        $catalog_model->parent_id = $catalog->id;
        $catalog_model->catalog_name = $name;
        $catalog_model->uid = $catalog->uid;
        DB::beginTransaction();
        $update_lef = $catalog_model->where('lef', '>=', $catalog->rig)->increment('lef', 2);
        $update_rig = $catalog_model->where('rig', '>=', $catalog->rig)->increment('rig', 2);
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
     * @param $from_catalog_id
     * @param $to_catalog_id
     * @param int $type 0:只转移当前目录下的所有文章 1:转移当前目录和其下所有子目录的文章
     */
    public function moveCatalogBlog($from_catalog_id, $to_catalog_id, $type = 1)
    {
        $catalog_info = Catalog::where('id', $from_catalog_id)->first();
        if($type == 2){
            //所有的子目录
            $catalog_ids = Catalog::where('lef', '>=', $catalog_info->lef)
                ->where('rig', '<=', $catalog_info->rig)
                ->pluck('id');
        }else{
            //所有的子目录
            $catalog_ids = Catalog::where('lef', '=', $catalog_info->lef)
                ->where('rig', '=', $catalog_info->rig)
                ->pluck('id');
        }
        try {
            BlogList::whereIn('catalog_id', $catalog_ids)->update(['catalog_id' => $to_catalog_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}