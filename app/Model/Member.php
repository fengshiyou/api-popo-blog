<?php

namespace App\Model;

use App\Services\CatalogServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Member extends Model
{
    //
    protected $primaryKey = 'uid';
    protected $table = "member";
    protected function sql_init()
    {
        //删除表
        $del_sql = 'DROP TABLE  IF EXISTS `'. self::getTable() . '`;';
        echo $del_sql;
        DB::connection($this->connection)->statement($del_sql);
        //创建表
        $sql = "
    CREATE TABLE `". self::getTable() . "` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `acount` varchar(50) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `passwd` varchar(64) NOT NULL,
  `solt` varchar(4) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否能供使用',
  `power_role_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限组ID',
  `icon_url` varchar(255) DEFAULT NULL COMMENT '头像链接',
  `header_welcome` varchar(255) DEFAULT NULL COMMENT '头部导航欢迎语',
  `header_graph` varchar(255) DEFAULT NULL COMMENT '头部签名',
  `motto` varchar(255) DEFAULT NULL COMMENT '座右铭',
  `link1` varchar(255) DEFAULT NULL COMMENT '超链接1',
  `link1_des` varchar(255) DEFAULT NULL COMMENT '超链接1描述',
  `link2` varchar(255) DEFAULT NULL COMMENT '超链接2',
  `link2_des` varchar(255) DEFAULT NULL COMMENT '超链接2描述',
  `link3` varchar(255) DEFAULT NULL COMMENT '超链接3',
  `link3_des` varchar(255) DEFAULT NULL COMMENT '超链接3描述',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
";
        DB::connection($this->connection)->statement($sql);
        $this->initMember();
    }
    public function initMember(){
        $member = new Member();
        $member->acount = "fsy";
        $member->passwd = 'b76a2208aa77a8d52c166688644fed0e';
        $member->solt = 'pSrh';
        $member->enabled = 1;//启用
        $member->power_role_id = 1;//超级管理员
        $member->icon_url = '';
        $member->header_welcome = '欢迎光临popo的博客';
        $member->header_graph = '少整些没用的';
        $member->motto = 'never save never12';
        $member->link1 = 'https://github.com/fengshiyou';
        $member->link1_des = 'GitHub';
        $member->link2 = '';
        $member->link2_des = '';
        $member->link3 = '';
        $member->link3_des = '';
        $member->created_at = '2018-06-25 22:32:12';
        $member->updated_at = '2018-06-25 22:32:12';
        $member->save();
        //初始化用户目录
        $catalog_service = new CatalogServices();
        $catalog_service->initCatalog($member);
    }
}
