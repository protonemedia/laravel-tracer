<?php

namespace ProtoneMedia\LaravelTracer\Tests;

use Illuminate\Foundation\Auth\User;
use ProtoneMedia\LaravelTracer\Middleware\TraceUser;

abstract class AbstractTest extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        include_once __DIR__ . '/../database/migrations/create_user_requests_table.php.stub';
        (new \CreateUserRequestsTable)->up();

        $this->app['router']->group(['middleware' => TraceUser::class], function ($router) {
            $this->setupRoutes($router);
        });
    }

    abstract protected function setupRoutes($router);

    protected function getPackageProviders($app)
    {
        return [
            'ProtoneMedia\LaravelTracer\LaravelTracerServiceProvider',
        ];
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'ProtoneMedia\LaravelTracer\Tests\TestHttpKernel');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function user($id = 10)
    {
        $user     = new User;
        $user->id = $id;
        return $user;
    }

    protected function makeGetRequest($uri, $userId = 10)
    {
        $this->actingAs($this->user($userId))
            ->json('GET', $uri)
            ->assertStatus(200);
    }

    protected function assertQualification($qualifier, $userId = 10, $requestId = 1)
    {
        $this->assertDatabaseHas('user_requests', [
            'id'              => $requestId,
            'user_id'         => $userId,
            'qualified_route' => $qualifier,
        ]);
    }

    protected function assertGetRequestQualifiesAs($uri, $qualifier, $requestId = 1, $userId = 10)
    {
        $this->makeGetRequest($uri, $userId);
        $this->assertQualification($qualifier, $userId, $requestId);
    }
}
