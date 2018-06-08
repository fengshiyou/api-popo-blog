<?php

namespace App\Http\Controllers\log;

use App\model\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    //
    public function getList()
    {//页数
        $page_no = request()->get('page_no') ? request()->get('page_no') : 1;
        //每页数量
        $per_page = request()->get('per_page') > 0 && request()->get('per_page') <= 10 ? request()->get('per_page') : 10;
        $log = new Log();
        $total = $log->count();
        $data = $log->skip(($page_no - 1) * $per_page)
            ->take($per_page)
            ->orderBy('id', 'desc')
            ->get();

        return respSuc(['list' => $data, 'total' => $total]);
    }
}
