<?php

namespace App\Http\Middleware;

use Closure;

class BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $result = $this->match($request);
        if ($result === 0) {
            return $next($request);
        } else {
            return $this->noMatch($result);
        }
    }

    /**
     * 由子类重构
     * @return mixed
     */
    public function match($request)
    {
        return 0;
    }

    /**
     * 由子类重构
     * @return mixed
     */
    public function noMatch($result)
    {
        return $result;
    }
}
