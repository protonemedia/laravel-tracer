<?php

namespace ProtoneMedia\LaravelTracer\Tests;

use Illuminate\Foundation\Http\Kernel as BaseKernel;

class TestHttpKernel extends BaseKernel
{
    protected $routeMiddleware = [
        'qualify' => \ProtoneMedia\LaravelTracer\Middleware\QualifyRoute::class,
    ];
}
