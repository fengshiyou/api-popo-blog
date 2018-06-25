<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TableManage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:manage {model=ERROR} {option=sql_init}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '需要进行修改表结构时则维护该处';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = $this->argument('model');
        $option = $this->argument('option');
        var_dump($model) ;
        var_dump($option);
        if($model=="ERROR"){
            var_dump('---------请确认要进行表修改操作---------');
            $dir=__DIR__.'/../../Model';
            $file=scandir($dir);
            print_r($file);
            return false;
        }
        if($model =="all"&&$option=='all'){
            var_dump("--------即将进行初始化: ".$option.' '.$model.' ---------');
            for($i = 3 ;$i>0;$i--){
                var_dump(strval('--------- '.$i.' ---------'));
                sleep(1);
            }
            $dir=__DIR__.'/../../Model';
            $file=scandir($dir);
            foreach ($file as $v){
                if(strstr($v,'Trait')){
                    continue;
                }
                if(strstr($v,".php"))
                {
                    $tmp = "App\\Model\\".str_replace(".php","",$v);
                    $obj = new $tmp();
                    if(method_exists($obj,'sql_init')){
                        $tmp::sql_init();
                        var_dump($tmp."创建完毕");
                        if(method_exists($obj,'sql_data')){
                            $tmp::sql_data();
                            var_dump($tmp.'数据填充完毕');
                        }
                    }
                }
            }
            var_dump('---------初始化完毕---------');
//            User::userInit();//初始化管理员操作
            var_dump('---------管理员生成完毕---------');



            return ;
        }
        var_dump("---------即将进行表修改: ".$model.' ---------');
        for($i = 3 ;$i>0;$i--){
            var_dump(strval('--------- '.$i.' ---------'));
            sleep(1);
        }
        $table = "App\\Model\\$model";
        $table::$option();

        var_dump('---------处理完毕---------');
    }
}
