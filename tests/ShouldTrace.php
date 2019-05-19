<?php

namespace Protonemedia\LaravelTracer\Tests;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShouldTrace
{
    public function no(Request $request, Response $response): bool
    {
        return false;
    }

    public function yes(Request $request, Response $response): bool
    {
        return true;
    }
}
