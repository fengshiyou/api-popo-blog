<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tag extends Model
{
    public $timestamps = false;
    //
    protected $table = "tag";

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
  `name` varchar(255) NOT NULL DEFAULT '0' COMMENT '标签名',
  `color` varchar(255) NOT NULL DEFAULT 'red' COMMENT '标签颜色',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
        $this->initTags();
    }

    public function initTags()
    {
        $data = [
            [
                'name' => 'python',
                'color' => '#FF6600',
            ],
            [
                'name' => 'PHP',
                'color' => '#FF0067',
            ],
            [
                'name' => 'react',
                'color' => '#F700FF',
            ],
            [
                'name' => 'web前端',
                'color' => '#B500FF',
            ],
            [
                'name' => 'mysql',
                'color' => '#4A00FF',
            ],
            [
                'name' => 'linux',
                'color' => '#0075FF',
            ],
            [
                'name' => 'Laravel',
                'color' => '#00DBFF',
            ],
            [
                'name' => 'webpack',
                'color' => 'black',
            ],
        ];
        $tag = new Tag();
        $tag->insert($data);
    }
}
