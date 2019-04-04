<?php

namespace App\Http\Middleware;

use Closure;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class RequestLogMiddleware
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
        // create a logger for this middleware only
        $log = new Logger('HTTP_LOG');
        $date = date('Y-m-d');
        $log->pushHandler(new StreamHandler(storage_path()."/logs/requests/request_log_{$date}.log", Logger::INFO));

        // prepare the data
        $url = $request->fullUrl();
        $method = $request->getMethod();
        $content = json_encode($request->all());
        $ip = $request->getClientIp();
        $headers = json_encode($request->headers->all());
        $log_content = "Source(IP): {$ip} | Request Url: {$url} | Method: {$method} | Headers: {$headers} | Request Data: {$content}";

        // log the request and pass the request
        $log->info($log_content);
        return $next($request);
    }
}
