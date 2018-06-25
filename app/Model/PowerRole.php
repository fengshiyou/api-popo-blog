<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PowerRole extends Model
{
    //
    protected $table = 'power_role';

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
  `name` varchar(255) NOT NULL COMMENT '名称',
  `power` int(11) NOT NULL COMMENT '后端路由权限值 二进制和',
  `web_url_power` int(11) NOT NULL COMMENT '前端路由全限制 二进制和',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
        $this->initPowerRole();
    }

    public function initPowerRole()
    {
        $power_role = new PowerRole();
        $power_role->name = "超级管理员";
        $power_role->power = 2047;
        $power_role->web_url_power = 31;
        $power_role->updated_at = now();
        $power_role->created_at = now();
        $power_role->save();
    }
}
