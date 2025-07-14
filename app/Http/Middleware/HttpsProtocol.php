<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class HttpsProtocol
{
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && App::environment('production')) {
            // Kiểm tra header 'X-Forwarded-Proto'
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                return $next($request);
            }
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}