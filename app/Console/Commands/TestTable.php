<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Test1;
use App\Model\Test2;
class TestTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:make_test_date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试数据2018-09-18的分享';

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
        $data1 = [
            [
                'id'=>1,
                'name' => '1',
                'pid' => 0,
                'path'=> 0
            ],
            [
                'id' => 2,
                'name' => '1-1',
                'pid' => 1,
                'path'=> '0-1'
            ],
            [
                'id' => 3,
                'name' => '1-1-1',
                'pid' => 2,
                'path'=> '0-1-1'
            ],
            [
                'id' => 4,
                'name' => '1-1-2',
                'pid' => 2,
                'path'=> '0-1-2'
            ],
            [
                'id' => 5,
                'name' => '1-2',
                'pid' => 1,
                'path'=> '0-2'
            ],
        ];
        for ($i=0; $i < 1000; $i++) { 
            $data1[] = [
                'id' => $i + 6,
                'name' => '1-1-' . ($i + 3),
                'pid' => 2,
                'path'=> '0-1-' . ($i + 3)
            ];
        }
        for ($i=0; $i < 10000; $i++) { 
            $data1[] = [
                'id' => $i + 1006,
                'name' => '1-2-' . ($i + 1),
                'pid' => 5,
                'path'=> '0-2-' . ($i + 3)
            ];
        }
        $test1 = new Test1();
        $test1->insert($data1);
    }
}
