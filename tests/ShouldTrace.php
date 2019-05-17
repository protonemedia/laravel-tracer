<?php

namespace Protonemedia\LaravelTracer\Tests;

class ShouldTrace
{
    public function no()
    {
        return false;
    }

    public function yes()
    {
        return true;
    }
}
