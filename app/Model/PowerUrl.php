<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PowerUrl extends Model
{
    //
    protected $table = 'power_url';

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
  `url` varchar(255) NOT NULL COMMENT '地址',
  `power_mark` int(11) NOT NULL COMMENT '权限标识   二进制',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='权限地址   需要权限验证的地址';
";
        DB::connection($this->connection)->statement($sql);
        $this->initPowerUrl();
    }

    public function initPowerUrl()
    {
        $data = [
            [
                'name' => '设置权限路由',
                'url' => '/api/power/setPower',
                'power_mark' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '新增权限路由',
                'url' => '/api/power/addPowerRole',
                'power_mark' => 2,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '删除权限路由',
                'url' => '/api/power/delPowerUrl',
                'power_mark' => 4,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '新增权限组',
                'url' => '/api/power/addPowerRole',
                'power_mark' => 8,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '删除权限组',
                'url' => '/api/power/delPowerRole',
                'power_mark' => 16,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '设置前端路由',
                'url' => '/api/power/setWebUrlPower',
                'power_mark' => 32,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '重置用户密码',
                'url' => '/api/user/resetPasswd',
                'power_mark' => 64,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '启用禁用用户',
                'url' => '/api/user/setEnabled',
                'power_mark' => 128,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '设置用户权限组',
                'url' => '/api/user/setPowerRole',
                'power_mark' => 256,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '新增/修改标签',
                'url' => '/api/tag/edit',
                'power_mark' => 512,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => '删除标签',
                'url' => '/api/tag/del',
                'power_mark' => 1024,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ];
        $power_url = new PowerUrl();
        $power_url->insert($data);
    }
}
