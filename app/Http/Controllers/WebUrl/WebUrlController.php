<?php

namespace App\Http\Controllers\WebUrl;

use App\model\WebUrl;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebUrlController extends Controller
{
    //
    public function getList(){
        $data = WebUrl::get();
        return respSuc($data);
    }
    public function setPower(){

    }
}
