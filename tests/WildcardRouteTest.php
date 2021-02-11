<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Response;

class WildcardRouteTest extends TestCase
{
    /** @test */
    public function test_permission_function()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $router = $this->getRouter();

        $router->get('permission-test', $this->getRouteResponse())
                ->name('permission.test')
                ->permission(['articles.edit', 'articles.save']);

        $this->assertEquals(['permission:articles.edit|articles.save'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_group_and_permission_function_together()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $router = $this->getRouter();

        $router->get('group-permission-test', $this->getRouteResponse())
                ->name('group-permission.test')
                ->group('superadmin|admin')
                ->permission('user.create|user.edit');

        $this->assertEquals(
            [
                'group:superadmin|admin',
                'permission:user.create|user.edit',
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
