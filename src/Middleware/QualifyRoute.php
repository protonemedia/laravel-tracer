<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;

class QualifyRoute
{
    public static $qualifiedRoutes = [];

    public static function forRequest($request, $name, int $seconds = null)
    {
        static::$qualifiedRoutes[$request->route()->uri()] = compact('name', 'seconds');
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
