<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    public $timestamps = false;
    //表名
    protected $table = "catalog";

    public function scopeGetCatalogList($query, $uid, $catalog_id = false)
    {
        $query = $query->select('catalog.*');
        $query = $query->where('catalog.uid', $uid);
        $query = $query->leftJoin('blog_list', 'blog_list.catalog_id',"=","catalog.id");
        return $query;
    }
}
