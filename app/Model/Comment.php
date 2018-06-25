<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Comment extends Model
{
    //
    protected $table = 'comment';
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
  `type` varchar(10) DEFAULT '0' COMMENT '1:对个人留言 2:对博客留言 3:对留言内容留言',
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT 'uid或者博客id或者留言id',
  `content` text COMMENT '内容',
  `reply_count` int(11) NOT NULL,
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
