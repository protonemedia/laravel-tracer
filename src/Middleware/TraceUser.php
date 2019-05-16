<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Protonemedia\LaravelTracer\UserRequest;

class TraceUser
{
    private $limiter;

    /**
     * Sets the Rate Limiter.
     *
     * @param \Illuminate\Cache\RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request and trace the user
     * if the current user is authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$user = $request->user()) {
            return $response;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }

        $this->traceUserRequest($user, $request);

        return $response;
    }

    /**
     * Stores the user request in the database.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Protonemedia\LaravelTracer\UserRequest|null
     */
    public function traceUserRequest(UserContract $user, Request $request):  ? UserRequest
    {
        $qualified = $this->qualify($request, $request->route());

        if ($this->tooManyAttempts($qualified)) {
            return null;
        }

        return UserRequest::create([
            'user_id'         => $user->getAuthIdentifier(),
            'qualified_route' => $qualified['name'],
        ]);
    }

    /**
     * Returns the qualified name for the given request and route data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Routing\Route   $route
     * @return mixed
     */
    private function qualify(Request $request, Route $route) : array
    {
        if (!$qualified = QualifyRoute::getByUri($route->uri())) {
            return [
                'name'                 => $route->getName() ?: $request->path(),
                'seconds_between_logs' => null,
            ];
        }

        Collection::make($route->parameters())->map(function ($value, $parameter) use (&$qualified) {
            $qualified['name'] = str_replace("{{$parameter}}", $value, $qualified['name']);
        });

        return $qualified;
    }

    /**
     * Returns a boolean wether this request has been attemped to
     * trace too many times.
     *
     * @param  array  $qualified
     * @return boolean
     */
    private function tooManyAttempts(array $qualified): bool
    {
        if (!$qualified['seconds_between_logs']) {
            return false;
        }

        if ($this->limiter->tooManyAttempts($qualified['name'], 1)) {
            return true;
        }

        $this->limiter->hit($qualified['name'], $qualified['seconds_between_logs']);

        return false;
    }
}
