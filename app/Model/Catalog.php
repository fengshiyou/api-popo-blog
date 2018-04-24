<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Catalog extends Model
{
    public $timestamps = false;
    //表名
    protected $table = "catalog";

    /**
     * 获取目录列表
     * @param $query
     * @param $uid
     * @return mixed
     */
    public function scopeGetCatalogListHaveCount($query, $uid)
    {
        $query = $query->select('catalog.*', DB::raw('count(if(blog_list.id>0,true,null)) as count'));
        $query = $query->where('catalog.uid', $uid);
        $query = $query->leftJoin('blog_list', 'blog_list.catalog_id', "=", "catalog.id");
        $query = $query->groupBy('catalog.id');
        return $query;
    }
}
