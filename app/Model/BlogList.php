<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BlogList extends Model
{
    public $timestamps = false;
    //表名
    protected $table = "blog_list";

    //查询文章目录
    public function scopeGetCatalog($query)
    {
        $query = $query->addSelect(DB::raw('group_concat(catalog2.catalog_name,"|",catalog2.id,"|",catalog2.parent_id) as catalog'));
        $query = $query->leftJoin('catalog as catalog1', 'catalog1.id', '=', 'blog_list.catalog_id');
        $query = $query->leftJoin('catalog as catalog2', function ($join) {
            $join
                ->on('catalog2.lef', '<=', 'catalog1.lef')
                ->on('catalog2.rig', '>=', 'catalog1.rig')
                ->on('catalog2.uid', '=', 'catalog1.uid');
        });
        $query->orderBy('catalog2.lef');
        $query->groupBy('blog_list.id');

        return $query;
    }
}
