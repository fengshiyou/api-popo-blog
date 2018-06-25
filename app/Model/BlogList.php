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

    // 创建数据库语句
    protected function sql_init()
    {
        //删除表
        $del_sql = 'DROP TABLE  IF EXISTS `' . self::getTable() . '`;';
        echo $del_sql;
        DB::connection($this->connection)->statement($del_sql);
        //创建表
        $sql = "
    CREATE TABLE `" . self::getTable() . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `title` varchar(255) NOT NULL DEFAULT '0' COMMENT '标题',
  `content_id` int(11) DEFAULT '0' COMMENT '内容ID',
  `catalog_id` int(11) DEFAULT '0' COMMENT '目录ID',
  `tags` varchar(255) DEFAULT '0' COMMENT '标签id  |12|23434|234|2323',
  `comment_count` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
    }
}
