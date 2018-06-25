<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Reply extends Model
{
    //
    protected $table = 'reply';
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
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '留言者ID',
  `comment_id` int(11) NOT NULL DEFAULT '0' COMMENT '评论ID',
  `content` text COMMENT '内容',
  `to_uid` int(11) DEFAULT NULL COMMENT '对谁回复',
  `lef` int(11) NOT NULL DEFAULT '0' COMMENT '左值 暂时没用',
  `rig` int(11) NOT NULL DEFAULT '0' COMMENT '右值 暂时没用',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父ID 暂时没用',
  `remove` tinyint(4) NOT NULL COMMENT '是否删除 ',
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
    }
}
