<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class LocalApiProtection
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
        if ($request->server('SERVER_NAME') !==  env('APP_SERVER_NAME')) {
            return new Response('Invalid credentials.', 401, ['WWW-Authenticate' => 'Customize']);
        }
        return $next($request);
    }
}
