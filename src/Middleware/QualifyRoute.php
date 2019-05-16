<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Http\Request;

class QualifyRoute
{
    private static $qualifiedRoutes = [];

    public static function forRequest(Request $request, string $name, int $secondsBetweenLogs = null)
    {
        static::$qualifiedRoutes[$request->route()->uri()] = [
            'name'                 => $name,
            'seconds_between_logs' => $secondsBetweenLogs,
        ];
    }

    public static function getByUri(string $uri):  ? array
    {
        return static::$qualifiedRoutes[$uri] ?? null;
    }

    /**
     * Put the request URI and name to the static $qualified array
     * and handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $name
     * @param  int  $seconds
     * @return mixed
     */
    public function handle($request, Closure $next, $name, int $seconds = null)
    {
        static::forRequest($request, $name, $seconds);

        return $next($request);
    }
}
