<?php

namespace Protonemedia\LaravelTracer\Tests;

use Illuminate\Foundation\Http\Kernel as BaseKernel;

class TestHttpKernel extends BaseKernel
{
    protected $routeMiddleware = [
        'qualify' => \Protonemedia\LaravelTracer\Middleware\QualifyRoute::class,
    ];
}
