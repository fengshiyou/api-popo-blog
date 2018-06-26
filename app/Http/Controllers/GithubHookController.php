<?php

namespace App\Http\Controllers;

use Github\Hook\GithubHook;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class GithubHookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if(env("APP_DEBUG")){
            $hookModel = new GithubHook();
            $input_passwd = Request()->input('passwd');
            $hook_passwd = config('config.hook.hook_secret');
            if ($input_passwd == $hook_passwd){
                $hookModel->git_pull();
            }else{
                $hookModel->actionGit();
            }
            $this->make_apidoc();
        }
    }
    public function make_apidoc(){
        $path = ' /data/www/api-popo-blog';
        $passwd = 'Ace___7';
        $shell_command = "cd $path && echo '$passwd' | /usr/bin/sudo -S sh apidoc.sh ";
        shell_exec($shell_command);
        var_dump($shell_command);
    }

}
