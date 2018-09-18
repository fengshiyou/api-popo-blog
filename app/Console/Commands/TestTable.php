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
                'test' => 'python',
            ],
            [
                'test' => 'PHP',
            ],
            [
                'test' => 'react',
            ],
            [
                'test' => 'web前端',
            ],
            [
                'test' => 'mysql',
            ],
            [
                'test' => 'linux',
            ],
            [
                'test' => 'Laravel',
            ],
            [
                'test' => 'webpack',
            ],
        ];
        $test1 = new Test1();
        $test1->insert($data1);
    }
}
