<?php

namespace App\Http\Middleware;

use Closure;
use App\Listeners\EventListener;

class ApiManagerMiddleware
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
        $response = $next($request);
        $listener = app()->make(EventListener::class);
        $listener->onRequestHandled($request, $response);
        return $response;
    }
}
