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
    protected function sql_init()
    {
        //删除表
        $del_sql = 'DROP TABLE  IF EXISTS `'. self::getTable() . '`;';
        echo $del_sql;
        DB::connection($this->connection)->statement($del_sql);
        //创建表
        $sql = "
    CREATE TABLE `". self::getTable() . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `catalog_name` varchar(255) NOT NULL DEFAULT '0' COMMENT '目录名称',
  `lef` int(11) NOT NULL DEFAULT '0' COMMENT '左值',
  `rig` int(11) NOT NULL DEFAULT '0' COMMENT '右值',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父ID',
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
    }
}
