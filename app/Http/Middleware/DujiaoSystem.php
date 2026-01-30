<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

class DujiaoSystem
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 检测 HTTPS：直接连接或经反向代理 (X-Forwarded-Proto)
        if (
            $request->getScheme() === 'https'
            || $request->header('X-Forwarded-Proto') === 'https'
            || config('app.admin_https', false)
        ) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
