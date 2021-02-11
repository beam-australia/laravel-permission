<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\GroupMiddleware;

class GroupMiddlewareTest extends TestCase
{
    protected $groupMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->groupMiddleware = new GroupMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_groupmiddleware()
    {
        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, 'testGroup')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_group_middleware_of_another_guard()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, 'testAdminGroup')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_group_middleware_if_have_this_group()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupMiddleware, 'testGroup')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_group_middleware_if_have_one_of_the_groups()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupMiddleware, 'testGroup|testGroup2')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupMiddleware, ['testGroup2', 'testGroup'])
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_group_middleware_if_have_a_different_group()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup(['testGroup']);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, 'testGroup2')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_group_middleware_if_have_not_groups()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, 'testGroup|testGroup2')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_group_middleware_if_group_is_undefined()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, '')
        );
    }

    /** @test */
    public function the_required_groups_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredGroups = [];

        try {
            $this->groupMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-group');
        } catch (UnauthorizedException $e) {
            $requiredGroups = $e->getRequiredGroups();
        }

        $this->assertEquals(['some-group'], $requiredGroups);
    }

    /** @test */
    public function use_not_existing_custom_guard_in_group()
    {
        $class = null;

        try {
            $this->groupMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'testGroup', 'xxx');
        } catch (InvalidArgumentException $e) {
            $class = get_class($e);
        }

        $this->assertEquals(InvalidArgumentException::class, $class);
    }

    /** @test */
    public function user_can_not_access_group_with_guard_admin_while_using_default_guard()
    {
        Auth::login($this->testUser);

        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->groupMiddleware, 'testGroup', 'admin')
        );
    }

    /** @test */
    public function user_can_access_group_with_guard_admin_while_using_default_guard()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignGroup('testAdminGroup');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->groupMiddleware, 'testAdminGroup', 'admin')
        );
    }

    protected function runMiddleware($middleware, $groupName, $guard = null)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $groupName, $guard)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
