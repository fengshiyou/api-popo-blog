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

        $data = new Log();
        //账号搜索
        if (request()->get('acount_search')) {
            $data = $data->where('acount', 'like', "%".request()->get('acount_search')."%");
        }
        //动作搜索
        if (request()->get('action_search')) {
            $data = $data->where('action', 'like', "%".request()->get('action_search')."%");
        }
        //参数搜索
        if (request()->get('params_search')) {
            $data = $data->where('params', 'like', "%".request()->get('params_search')."%");
        }
        //ip搜索
        if (request()->get('client_ip_search')) {
            $data = $data->where('client_ip', 'like', "%".request()->get('client_ip_search')."%");
        }
        $total = $data->count();
        $data = $data->skip(($page_no - 1) * $per_page);
        $data = $data->take($per_page)
            ->orderBy('id', 'desc')
            ->get();

        return respSuc(['list' => $data, 'total' => $total,'page_no'=>$page_no]);
    }
}
