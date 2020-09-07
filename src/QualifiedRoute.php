<?php

namespace ProtoneMedia\LaravelTracer;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

class QualifiedRoute
{
    /**
     * Name of the qualified route.
     *
     * @var string
     */
    private $name;

    /**
     * Seconds between the logs of the qualified route.
     *
     * @var bool|int|null
     */
    private $secondsBetweenLogs;

    /**
     * Setups this instance.
     *
     * @param \Illuminate\Routing\Route  $route
     * @param string $name
     * @param bool|int|null $secondsBetweenLogs
     */
    public function __construct(Route $route, string $name, $secondsBetweenLogs = null)
    {
        $this->name               = $this->replaceParameters($name, $route->parameters());
        $this->secondsBetweenLogs = is_null($secondsBetweenLogs) ? config('laravel-tracer.seconds_between_logs') : $secondsBetweenLogs;
    }

    /**
     * Replaces the parameters within the qualified route with
     * the actual given parameters (of the route).
     *
     * @param  string $name
     * @param  array  $parameters
     *
     * @return string
     */
    private function replaceParameters(string $name, array $parameters): string
    {
        Collection::make($parameters)->map(function ($value, $parameter) use (&$name) {
            $name = str_replace("{{$parameter}}", $value, $name);
        });

        return $name;
    }

    /**
     * Returns a new instance based on the given request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public static function fromRequest(Request $request)
    {
        return new static(
            $route = $request->route(),
            $name = $route->getName() ?: $request->path()
        );
    }

    /**
     * Getter for the name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Getter fot the seconds between logs.
     *
     * @return mixed
     */
    public function secondsBetweenLogs()
    {
        return $this->secondsBetweenLogs;
    }
}
