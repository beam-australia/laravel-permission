<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\GroupOrPermissionMiddleware;

class GroupOrPermissionMiddlewareTest extends TestCase
{
    protected $groupOrPermissionMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->groupOrPermissionMiddleware = new GroupOrPermissionMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_group_or_permission_middleware()
    {
        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_group_middleware_if_has_this_permission_or_group()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|edit-news|edit-articles')
        );

        $this->testUser->removeGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|edit-articles')
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|edit-articles')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, ['testGroup', 'edit-articles'])
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_group_middleware_if_have_not_this_permission_and_group()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'testGroup|edit-articles')
        );

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'missingGroup|missingPermission')
        );
    }

    /** @test */
    public function use_not_existing_custom_guard_in_group_or_permission()
    {
        $class = null;

        try {
            $this->groupOrPermissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'testGroup', 'xxx');
        } catch (InvalidArgumentException $e) {
            $class = get_class($e);
        }

        $this->assertEquals(InvalidArgumentException::class, $class);
    }

    /** @test */
    public function user_can_not_access_permission_or_group_with_guard_admin_while_using_default_guard()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'edit-articles|testGroup', 'admin')
        );
    }

    /** @test */
    public function user_can_access_permission_or_group_with_guard_admin_while_using_default_guard()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignGroup('testAdminGroup');
        $this->testAdmin->givePermissionTo('admin-permission');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupOrPermissionMiddleware, 'admin-permission|testAdminGroup', 'admin')
        );
    }

    protected function runMiddleware($middleware, $name, $guard = null)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $name, $guard)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
