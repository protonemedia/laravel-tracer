<?php

namespace Protonemedia\LaravelTracer\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Protonemedia\LaravelTracer\Middleware\QualifyRoute;
use Protonemedia\LaravelTracer\UserRequest;
use Symfony\Component\HttpFoundation\Response;

class TraceUser
{
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

        if (($user = $request->user()) && $this->isSuccessful($response)) {
            $this->traceUserRequest($user, $request);
        }

        return $response;
    }

    /**
     * Returns a boolean wether the response is successful.
     *
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * @return bool
     */
    private function isSuccessful(Response $response): bool
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }

    /**
     * Stores the user request in the database.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Protonemedia\LaravelTracer\UserRequest|null
     */
    public function traceUserRequest(UserContract $user, Request $request)
    {
        $qualifiedRoute = $this->qualify(
            $this->gatherRequestData($request, $request->route())
        );

        if ($qualifiedRoute['seconds']) {
            $limiter = app(RateLimiter::class);

            if ($limiter->tooManyAttempts($qualifiedRoute['name'], 1)) {
                return;
            }

            $limiter->hit($qualifiedRoute['name'], $qualifiedRoute['seconds']);
        }

        return UserRequest::create([
            'user_id'         => $user->getAuthIdentifier(),
            'qualified_route' => $qualifiedRoute['name'],
        ]);
    }

    /**
     * Returns an array of relevant request and route data.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Routing\Route   $route
     * @return array
     */
    private function gatherRequestData(Request $request, Route $route): array
    {
        return [
            'method'     => $request->method(),
            'path'       => $request->path(),
            'name'       => $route->getName(),
            'uses'       => $route->getAction()['uses'],
            'parameters' => $route->parameters(),
            'uri'        => $route->uri(),
        ];
    }

    /**
     * Returns the qualified name for the given request and route data.
     *
     * @param  array  $data
     * @return mixed
     */
    private function qualify(array $data): array
    {
        $routes = QualifyRoute::$qualifiedRoutes;

        if (!array_key_exists($data['uri'], $routes)) {
            return [
                'name'    => $data['name'] ?: $data['path'],
                'seconds' => null,
            ];
        }

        $qualified = $routes[$data['uri']];

        Collection::make($data['parameters'])->map(function ($value, $parameter) use (&$qualified) {
            $qualified['name'] = str_replace("{{$parameter}}", $value, $qualified['name']);
        });

        $qualified['seconds'] = $qualified['seconds'] ?? null;

        return $qualified;
    }
}
