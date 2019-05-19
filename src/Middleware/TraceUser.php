<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Protonemedia\LaravelTracer\QualifiedRoute;
use Protonemedia\LaravelTracer\UserRequest;
use Symfony\Component\HttpFoundation\Response;

class TraceUser
{
    /**
     * Rate Limiter
     *
     * @var \Illuminate\Cache\RateLimiter
     */
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

        $this->traceUserRequest($user, $request, $response);

        return $response;
    }

    /**
     * Stores the user request in the database.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     *
     * @return \Protonemedia\LaravelTracer\UserRequest|null
     */
    private function traceUserRequest(UserContract $user, Request $request, Response $response):  ? UserRequest
    {
        $qualified = $request->qualifiedRoute();

        if ($this->tooManyAttempts($qualified)) {
            return null;
        }

        if (!$this->shouldTraceUser($request, $response)) {
            return null;
        }

        return UserRequest::create([
            'user_id'         => $user->getAuthIdentifier(),
            'qualified_route' => $qualified->name(),
        ]);
    }

    /**
     * Returns a boolean wether this request has been attemped to
     * trace too many times.
     *
     * @param  array  $qualified
     * @return boolean
     */
    private function tooManyAttempts($qualified) : bool
    {
        if (!$secondsBetweenLogs = $qualified->secondsBetweenLogs()) {
            return false;
        }

        if ($this->limiter->tooManyAttempts($qualified->name(), 1)) {
            return true;
        }

        $this->limiter->hit($qualified->name(), $secondsBetweenLogs);

        return false;
    }

    /**
     * Calls the callable method that can be specified in the config file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return boolean
     */
    private function shouldTraceUser(Request $request, Response $response): bool
    {
        if (!$callable = config('laravel-tracer.should_trace_user')) {
            return true;
        }

        return app()->call($callable, [$request, $response]);
    }
}
