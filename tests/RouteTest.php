<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Response;

class RouteTest extends TestCase
{
    /** @test */
    public function test_group_function()
    {
        $router = $this->getRouter();

        $router->get('group-test', $this->getRouteResponse())
                ->name('group.test')
                ->group('superadmin');

        $this->assertEquals(['group:superadmin'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_permission_function()
    {
        $router = $this->getRouter();

        $router->get('permission-test', $this->getRouteResponse())
                ->name('permission.test')
                ->permission(['edit articles', 'save articles']);

        $this->assertEquals(['permission:edit articles|save articles'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_group_and_permission_function_together()
    {
        $router = $this->getRouter();

        $router->get('group-permission-test', $this->getRouteResponse())
                ->name('group-permission.test')
                ->group('superadmin|admin')
                ->permission('create user|edit user');

        $this->assertEquals(
            [
                'group:superadmin|admin',
                'permission:create user|edit user',
            ],
            $this->getLastRouteMiddlewareFromRouter($router)
        );
    }

    protected function getLastRouteMiddlewareFromRouter($router)
    {
        return last($router->getRoutes()->get())->middleware();
    }

    protected function getRouter()
    {
        return app('router');
    }

    protected function getRouteResponse()
    {
        return function () {
            return (new Response())->setContent('<html></html>');
        };
    }
}
