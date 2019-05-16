<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Http\Request;

class QualifyRoute
{
    /**
     * Array holding the qualified routes.
     *
     * @var array
     */
    private static $qualifiedRoutes = [];

    /**
     * Set the qualified name and limit interval for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string   $name
     * @param  int|null $secondsBetweenLogs
     *
     * @return void
     */
    public static function forRequest(Request $request, string $name, int $secondsBetweenLogs = null)
    {
        static::$qualifiedRoutes[$request->route()->uri()] = [
            'name'                 => $name,
            'seconds_between_logs' => $secondsBetweenLogs,
        ];
    }

    /**
     * Returns the qualified data for the given uri.
     *
     * @param  string $uri
     * @return array
     */
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
