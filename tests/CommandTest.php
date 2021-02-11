<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Group;

class CommandTest extends TestCase
{
    /** @test */
    public function it_can_create_a_group()
    {
        Artisan::call('permission:create-group', ['name' => 'new-group']);

        $this->assertCount(1, Group::where('name', 'new-group')->get());
        $this->assertCount(0, Group::where('name', 'new-group')->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_group_with_a_specific_guard()
    {
        Artisan::call('permission:create-group', [
            'name' => 'new-group',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Group::where('name', 'new-group')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_permission()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_group_and_permissions_at_same_time()
    {
        Artisan::call('permission:create-group', [
            'name' => 'new-group',
            'permissions' => 'first permission | second permission',
        ]);

        $group = Group::where('name', 'new-group')->first();

        $this->assertTrue($group->hasPermissionTo('first permission'));
        $this->assertTrue($group->hasPermissionTo('second permission'));
    }

    /** @test */
    public function it_can_create_a_group_without_duplication()
    {
        Artisan::call('permission:create-group', ['name' => 'new-group']);
        Artisan::call('permission:create-group', ['name' => 'new-group']);

        $this->assertCount(1, Group::where('name', 'new-group')->get());
        $this->assertCount(0, Group::where('name', 'new-group')->first()->permissions);
    }

    /** @test */
    public function it_can_create_a_permission_without_duplication()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_show_permission_tables()
    {
        Artisan::call('permission:show');

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') !== false);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            // |               | testGroup | testGroup2 |
            $this->assertMatchesRegularExpression('/\|\s+\|\s+testGroup\s+\|\s+testGroup2\s+\|/', $output);

            // | edit-articles |  ·       |  ·        |
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        } else { // phpUnit 9/8
            $this->assertRegExp('/\|\s+\|\s+testGroup\s+\|\s+testGroup2\s+\|/', $output);
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/', $output);
        }

        Group::findByName('testGroup')->givePermissionTo('edit-articles');
        $this->reloadPermissions();

        Artisan::call('permission:show');

        $output = Artisan::output();

        // | edit-articles |  ·       |  ·        |
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        } else {
            $this->assertRegExp('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/', $output);
        }
    }

    /** @test */
    public function it_can_show_permissions_for_guard()
    {
        Artisan::call('permission:show', ['guard' => 'web']);

        $output = Artisan::output();

        $this->assertTrue(strpos($output, 'Guard: web') !== false);
        $this->assertTrue(strpos($output, 'Guard: admin') === false);
    }
}
