<?php

namespace Protonemedia\LaravelTracer\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Protonemedia\LaravelTracer\Middleware\QualifyRoute;

class TraceUserTest extends AbstractTest
{
    protected function setupRoutes($router)
    {
        $success = function () {
            return [];
        };

        $notFound = function () {
            abort(404);
        };

        $router->get('/route-without-name', $success);
        $router->get('/route-not-found', $notFound);

        $router->get('/route-without-name/{id}', $success);
        $router->get('/route-with-name', $success)->name('namedRoute');

        $router->get('/route-with-qualifier', $success)
            ->name('namedRoute')
            ->middleware('qualify:qualifiedRoute');

        $router->get('/route-with-qualifier/{id}', $success)
            ->name('namedRoute')
            ->middleware('qualify:qualifiedRouteWithParameter');

        $router->get('/route-with-qualifier/replace/{id}', $success)
            ->name('namedRoute')
            ->middleware('qualify:qualifiedRoute.{id}');

        $router->group(['prefix' => 'group', 'middleware' => ['qualify:same']], function ($router) use ($success) {
            $router->get('/default', $success)->name('group-default');
            $router->get('/override', $success)->name('group-default')->middleware('qualify:different');
        });

        $router->get('/limit', $success)
            ->middleware('qualify:limited,30');

        $router->get('/custom', function (Request $request) {
            QualifyRoute::forRequest($request, 'custom-qualifier');
            return [];
        });
    }

    /** @test */
    public function it_can_qualify_a_route_without_a_name_or_a_qualifier()
    {
        $this->assertGetRequestQualifiesAs('route-without-name', 'route-without-name');
        $this->assertGetRequestQualifiesAs('route-without-name/1', 'route-without-name/1', 2);
    }

    /** @test */
    public function it_doesnt_log_requests_without_an_authenticated_user()
    {
        $this->json('GET', 'route-without-name')
            ->assertStatus(200);

        $this->assertDatabaseMissing('user_requests', ['id' => 1]);
    }

    /** @test */
    public function it_doesnt_log_requests_that_fail()
    {
        $this->actingAs($this->user())
            ->json('GET', 'route-not-found')
            ->assertStatus(404);

        $this->assertDatabaseMissing('user_requests', ['id' => 1]);
    }

    /** @test */
    public function it_can_qualify_a_route_with_a_name_and_without_a_qualifier()
    {
        $this->assertGetRequestQualifiesAs('route-with-name', 'namedRoute');
    }

    /** @test */
    public function it_can_qualify_a_route_with_a_qualifier()
    {
        $this->assertGetRequestQualifiesAs('route-with-qualifier', 'qualifiedRoute');
    }

    /** @test */
    public function it_can_qualify_a_route_with_a_qualifier_and_parameter()
    {
        $this->assertGetRequestQualifiesAs('route-with-qualifier/1', 'qualifiedRouteWithParameter');
        $this->assertGetRequestQualifiesAs('route-with-qualifier/2', 'qualifiedRouteWithParameter', 2);
    }

    /** @test */
    public function it_can_replace_the_parameter_in_the_qualified_name()
    {
        $this->assertGetRequestQualifiesAs('route-with-qualifier/replace/1', 'qualifiedRoute.1');
    }

    /** @test */
    public function it_can_handle_group_qualifiers()
    {
        $this->assertGetRequestQualifiesAs('group/default', 'same');
    }

    /** @test */
    public function it_can_override_a_group_qualifier()
    {
        $this->assertGetRequestQualifiesAs('/group/override', 'different');
    }

    /** @test */
    public function it_can_limit_the_logs_with_a_second_optional_paramter()
    {
        $this->assertGetRequestQualifiesAs('/limit', 'limited');
        $this->assertGetRequestQualifiesAs('/limit', 'limited');

        $this->assertDatabaseMissing('user_requests', ['id' => 2]);

        Carbon::setTestNow(now()->addSeconds(35));

        $this->assertGetRequestQualifiesAs('/limit', 'limited', 2);
        $this->assertGetRequestQualifiesAs('/limit', 'limited');

        $this->assertDatabaseMissing('user_requests', ['id' => 3]);
    }

    /** @test */
    public function it_can_set_the_qualifier_from_the_handler_itself()
    {
        $this->assertGetRequestQualifiesAs('/custom', 'custom-qualifier');
    }
}
