<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\GroupMiddleware;
use Spatie\Permission\Middlewares\GroupOrPermissionMiddleware;
use Spatie\Permission\Models\Permission;

class WildcardMiddlewareTest extends TestCase
{
    protected $groupMiddleware;
    protected $permissionMiddleware;
    protected $groupOrPermissionMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->groupMiddleware = new GroupMiddleware();

        $this->permissionMiddleware = new PermissionMiddleware();

        $this->groupOrPermissionMiddleware = new GroupOrPermissionMiddleware();

        app('config')->set('permission.enable_wildcard_permission', true);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            403,
            $this->runMiddleware($this->permissionMiddleware, 'articles.edit')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles']);

        $this->testUser->givePermissionTo('articles');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->permissionMiddleware, 'articles.edit')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*.test']);

        $this->testUser->givePermissionTo('articles.*.test');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->permissionMiddleware, 'news.edit|articles.create.test')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->permissionMiddleware, ['news.edit', 'articles.create.test'])
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*']);

        $this->testUser->givePermissionTo('articles.*');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->permissionMiddleware, 'news.edit')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->permissionMiddleware, 'articles.edit|news.edit')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_group_middleware_if_has_this_permission_or_group()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*']);

        $this->testUser->assignGroup('testGroup');
        $this->testUser->givePermissionTo('articles.*');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|news.edit|articles.create')
        );

        $this->testUser->removeGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|articles.edit')
        );

        $this->testUser->revokePermissionTo('articles.*');
        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|articles.edit')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, ['testGroup', 'articles.edit'])
        );
    }

    /** @test */
    public function the_required_permissions_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredPermissions = [];

        try {
            $this->permissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'permission.some');
        } catch (UnauthorizedException $e) {
            $requiredPermissions = $e->getRequiredPermissions();
        }

        $this->assertEquals(['permission.some'], $requiredPermissions);
    }

    protected function runMiddleware($middleware, $parameter)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
