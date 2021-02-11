<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Permission;

class WildcardGroupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app('config')->set('permission.enable_wildcard_permission', true);

        Permission::create(['name' => 'other-permission']);

        Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
    }

    /** @test */
    public function it_can_be_given_a_permission()
    {
        Permission::create(['name' => 'posts.*']);
        $this->testUserGroup->givePermissionTo('posts.*');

        $this->assertTrue($this->testUserGroup->hasPermissionTo('posts.create'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_an_array()
    {
        Permission::create(['name' => 'posts.*']);
        Permission::create(['name' => 'news.*']);

        $this->testUserGroup->givePermissionTo(['posts.*', 'news.*']);

        $this->assertTrue($this->testUserGroup->hasPermissionTo('posts.create'));
        $this->assertTrue($this->testUserGroup->hasPermissionTo('news.create'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_multiple_arguments()
    {
        Permission::create(['name' => 'posts.*']);
        Permission::create(['name' => 'news.*']);

        $this->testUserGroup->givePermissionTo('posts.*', 'news.*');

        $this->assertTrue($this->testUserGroup->hasPermissionTo('posts.edit.123'));
        $this->assertTrue($this->testUserGroup->hasPermissionTo('news.view.1'));
    }

    /** @test */
    public function it_can_be_given_a_permission_using_objects()
    {
        $this->testUserGroup->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUserGroup->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_the_permission()
    {
        $this->assertFalse($this->testUserGroup->hasPermissionTo('other-permission'));
    }

    /** @test */
    public function it_returns_false_if_permission_does_not_exists()
    {
        $this->assertFalse($this->testUserGroup->hasPermissionTo('doesnt-exist'));
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_a_permission_object()
    {
        $permission = app(Permission::class)->findByName('other-permission');

        $this->assertFalse($this->testUserGroup->hasPermissionTo($permission));
    }

    /** @test */
    public function it_creates_permission_object_with_findOrCreate_if_it_does_not_have_a_permission_object()
    {
        $permission = app(Permission::class)->findOrCreate('another-permission');

        $this->assertFalse($this->testUserGroup->hasPermissionTo($permission));

        $this->testUserGroup->givePermissionTo($permission);

        $this->testUserGroup = $this->testUserGroup->fresh();

        $this->assertTrue($this->testUserGroup->hasPermissionTo('another-permission'));
    }

    /** @test */
    public function it_returns_false_when_a_permission_of_the_wrong_guard_is_passed_in()
    {
        $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

        $this->assertFalse($this->testUserGroup->hasPermissionTo($permission));
    }
}
