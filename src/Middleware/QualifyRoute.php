<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Http\Request;

class QualifyRoute
{
    /**
     * Trigger the qualifiedRoute method on the request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $name
     * @param  bool|int  $secondsBetweenLogs
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $name, $secondsBetweenLogs = null)
    {
        $request->qualifyRoute($name, $secondsBetweenLogs);

        return $next($request);
    }
}
