<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Content extends Model
{
    public $timestamps = false;
    //
    protected $table = "content";
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
  `content` text,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
    }
}
