<?php

namespace App\Http\Middleware;

use Closure;

class test
{
    protected $test;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $mid_params = ['login_uid'=>'1'];
        $request->merge($mid_params);//合并参数

        return $next($request);
    }
}
