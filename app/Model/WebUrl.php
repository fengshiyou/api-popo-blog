<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WebUrl extends Model
{
    /**
     * 前端路由限制   用户设置中的路由
     */
    protected $table = 'web_url';

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
  `web_url` varchar(255) NOT NULL,
  `power_mark` int(11) NOT NULL COMMENT '权限标识   二进制',
  `des` varchar(255) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
        $this->initWebUrl();
    }

    public function initWebUrl()
    {
        $data = [
            [
                'web_url'=>'/home/setting/tags',
                'power_mark'=>1,
                'des'=>'标签管理'
            ],
            [
                'web_url'=>'/home/setting/userManage',
                'power_mark'=>2,
                'des'=>'用户管理'
            ],
            [
                'web_url'=>'/home/setting/PowerGroup',
                'power_mark'=>4,
                'des'=>'权限组管理'
            ],
            [
                'web_url'=>'/home/setting/log',
                'power_mark'=>8,
                'des'=>'日志查看'
            ],
            [
                'web_url'=>'/home/setting/webUrl',
                'power_mark'=>16,
                'des'=>'前端路由权限管理'
            ],
        ];
        $web_url = new WebUrl();
        $web_url->insert($data);
    }
}
