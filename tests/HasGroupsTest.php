<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Group;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\GroupDoesNotExist;

class HasGroupsTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_group()
    {
        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $group = app(Group::class)->findOrCreate('testGroupInWebGuard', 'web');

        $this->assertFalse($this->testUser->hasGroup($group));

        $this->testUser->assignGroup($group);
        $this->assertTrue($this->testUser->hasGroup($group));
        $this->assertTrue($this->testUser->hasGroup($group->name));
        $this->assertTrue($this->testUser->hasGroup($group->name, $group->guard_name));
        $this->assertTrue($this->testUser->hasGroup([$group->name, 'fakeGroup'], $group->guard_name));
        $this->assertTrue($this->testUser->hasGroup($group->id, $group->guard_name));
        $this->assertTrue($this->testUser->hasGroup([$group->id, 'fakeGroup'], $group->guard_name));

        $this->assertFalse($this->testUser->hasGroup($group->name, 'fakeGuard'));
        $this->assertFalse($this->testUser->hasGroup([$group->name, 'fakeGroup'], 'fakeGuard'));
        $this->assertFalse($this->testUser->hasGroup($group->id, 'fakeGuard'));
        $this->assertFalse($this->testUser->hasGroup([$group->id, 'fakeGroup'], 'fakeGuard'));

        $group = app(Group::class)->findOrCreate('testGroupInWebGuard2', 'web');
        $this->assertFalse($this->testUser->hasGroup($group));
    }

    /** @test */
    public function it_can_assign_and_remove_a_group()
    {
        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->testUser->assignGroup('testGroup');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->testUser->removeGroup('testGroup');

        $this->assertFalse($this->testUser->hasGroup('testGroup'));
    }

    /** @test */
    public function it_removes_a_group_and_returns_groups()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->assignGroup('testGroup2');

        $this->assertTrue($this->testUser->hasGroup(['testGroup', 'testGroup2']));

        $groups = $this->testUser->removeGroup('testGroup');

        $this->assertFalse($groups->hasGroup('testGroup'));

        $this->assertTrue($groups->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_group_on_a_permission()
    {
        $this->testUserPermission->assignGroup('testGroup');

        $this->assertTrue($this->testUserPermission->hasGroup('testGroup'));

        $this->testUserPermission->removeGroup('testGroup');

        $this->assertFalse($this->testUserPermission->hasGroup('testGroup'));
    }

    /** @test */
    public function it_can_assign_a_group_using_an_object()
    {
        $this->testUser->assignGroup($this->testUserGroup);

        $this->assertTrue($this->testUser->hasGroup($this->testUserGroup));
    }

    /** @test */
    public function it_can_assign_a_group_using_an_id()
    {
        $this->testUser->assignGroup($this->testUserGroup->id);

        $this->assertTrue($this->testUser->hasGroup($this->testUserGroup));
    }

    /** @test */
    public function it_can_assign_multiple_groups_at_once()
    {
        $this->testUser->assignGroup($this->testUserGroup->id, 'testGroup2');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_assign_multiple_groups_using_an_array()
    {
        $this->testUser->assignGroup([$this->testUserGroup->id, 'testGroup2']);

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_does_not_remove_already_associated_groups_when_assigning_new_groups()
    {
        $this->testUser->assignGroup($this->testUserGroup->id);

        $this->testUser->assignGroup('testGroup2');

        $this->assertTrue($this->testUser->fresh()->hasGroup('testGroup'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_group_that_is_already_assigned()
    {
        $this->testUser->assignGroup($this->testUserGroup->id);

        $this->testUser->assignGroup($this->testUserGroup->id);

        $this->assertTrue($this->testUser->fresh()->hasGroup('testGroup'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_group_that_does_not_exist()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->assignGroup('evil-emperor');
    }

    /** @test */
    public function it_can_only_assign_groups_from_the_correct_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->assignGroup('testAdminGroup');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_group_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->assignGroup($this->testAdminGroup);
    }

    /** @test */
    public function it_ignores_null_groups_when_syncing()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->syncGroups('testGroup2', null);

        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_groups_from_a_string()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->syncGroups('testGroup2');

        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_groups_from_a_string_on_a_permission()
    {
        $this->testUserPermission->assignGroup('testGroup');

        $this->testUserPermission->syncGroups('testGroup2');

        $this->assertFalse($this->testUserPermission->hasGroup('testGroup'));

        $this->assertTrue($this->testUserPermission->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_multiple_groups()
    {
        $this->testUser->syncGroups('testGroup', 'testGroup2');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_multiple_groups_from_an_array()
    {
        $this->testUser->syncGroups(['testGroup', 'testGroup2']);

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_will_remove_all_groups_when_an_empty_array_is_passed_to_sync_groups()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->assignGroup('testGroup2');

        $this->testUser->syncGroups([]);

        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->assertFalse($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_will_sync_groups_to_a_model_that_is_not_persisted()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncGroups([$this->testUserGroup]);
        $user->save();

        $this->assertTrue($user->hasGroup($this->testUserGroup));
    }

    /** @test */
    public function calling_syncGroups_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncGroups('testGroup');
        $user->save();

        $user2 = new User(['email' => 'admin@user.com']);
        $user2->syncGroups('testGroup2');
        $user2->save();

        $this->assertTrue($user->fresh()->hasGroup('testGroup'));
        $this->assertFalse($user->fresh()->hasGroup('testGroup2'));

        $this->assertTrue($user2->fresh()->hasGroup('testGroup2'));
        $this->assertFalse($user2->fresh()->hasGroup('testGroup'));
    }

    /** @test */
    public function calling_assignGroup_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->assignGroup('testGroup');
        $user->save();

        $admin_user = new User(['email' => 'admin@user.com']);
        $admin_user->assignGroup('testGroup2');
        $admin_user->save();

        $this->assertTrue($user->fresh()->hasGroup('testGroup'));
        $this->assertFalse($user->fresh()->hasGroup('testGroup2'));

        $this->assertTrue($admin_user->fresh()->hasGroup('testGroup2'));
        $this->assertFalse($admin_user->fresh()->hasGroup('testGroup'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_a_group_from_another_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->syncGroups('testGroup', 'testAdminGroup');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->syncGroups('testGroup', $this->testAdminGroup);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user = User::create(['email' => 'user@test.com']);

        $user->assignGroup('testGroup');
        $user->givePermissionTo('edit-articles');

        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
        $this->assertDatabaseHas('model_has_groups', [config('permission.column_names.model_morph_key') => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
        $this->assertDatabaseMissing('model_has_groups', [config('permission.column_names.model_morph_key') => $user->id]);
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup('testGroup');
        $user2->assignGroup('testGroup2');

        $scopedUsers = User::group('testGroup')->get();

        $this->assertEquals(1, $scopedUsers->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group([$this->testUserGroup])->get();

        $scopedUsers2 = User::group(['testGroup', 'testGroup2'])->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(2, $scopedUsers2->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_array_of_ids_and_names()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        $user1->assignGroup($this->testUserGroup);

        $user2->assignGroup('testGroup2');

        $groupName = $this->testUserGroup->name;

        $otherGroupId = app(Group::class)->find(2)->id;

        $scopedUsers = User::group([$groupName, $otherGroupId])->get();

        $this->assertEquals(2, $scopedUsers->count());
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group([$this->testUserGroup])->get();
        $scopedUsers2 = User::group(collect(['testGroup', 'testGroup2']))->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(2, $scopedUsers2->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group($this->testUserGroup)->get();
        $scopedUsers2 = User::group([$this->testUserGroup])->get();
        $scopedUsers3 = User::group(collect([$this->testUserGroup]))->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(1, $scopedUsers3->count());
    }

    /** @test */
    public function it_can_scope_against_a_specific_guard()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup('testGroup');
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group('testGroup', 'web')->get();

        $this->assertEquals(1, $scopedUsers1->count());

        $user3 = Admin::create(['email' => 'user1@test.com']);
        $user4 = Admin::create(['email' => 'user1@test.com']);
        $user5 = Admin::create(['email' => 'user2@test.com']);
        $testAdminGroup2 = app(Group::class)->create(['name' => 'testAdminGroup2', 'guard_name' => 'admin']);
        $user3->assignGroup($this->testAdminGroup);
        $user4->assignGroup($this->testAdminGroup);
        $user5->assignGroup($testAdminGroup2);
        $scopedUsers2 = Admin::group('testAdminGroup', 'admin')->get();
        $scopedUsers3 = Admin::group('testAdminGroup2', 'admin')->get();

        $this->assertEquals(2, $scopedUsers2->count());
        $this->assertEquals(1, $scopedUsers3->count());
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_group_from_another_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        User::group('testAdminGroup')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::group($this->testAdminGroup)->get();
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_non_existing_group()
    {
        $this->expectException(GroupDoesNotExist::class);

        User::group('group not defined')->get();
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_groups()
    {
        $groupModel = app(Group::class);

        $groupModel->create(['name' => 'second group']);

        $this->assertFalse($this->testUser->hasGroup($groupModel->all()));

        $this->testUser->assignGroup($this->testUserGroup);

        $this->assertTrue($this->testUser->hasGroup($groupModel->all()));

        $this->assertTrue($this->testUser->hasAnyGroup($groupModel->all()));

        $this->assertTrue($this->testUser->hasAnyGroup('testGroup'));

        $this->assertFalse($this->testUser->hasAnyGroup('group does not exist'));

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup']));

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup', 'group does not exist']));

        $this->assertFalse($this->testUser->hasAnyGroup(['group does not exist']));

        $this->assertTrue($this->testUser->hasAnyGroup('testGroup', 'group does not exist'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_groups()
    {
        $groupModel = app(Group::class);

        $this->assertFalse($this->testUser->hasAllGroups($groupModel->first()));

        $this->assertFalse($this->testUser->hasAllGroups('testGroup'));

        $this->assertFalse($this->testUser->hasAllGroups($groupModel->all()));

        $groupModel->create(['name' => 'second group']);

        $this->testUser->assignGroup($this->testUserGroup);

        $this->assertTrue($this->testUser->hasAllGroups('testGroup'));
        $this->assertTrue($this->testUser->hasAllGroups('testGroup', 'web'));
        $this->assertFalse($this->testUser->hasAllGroups('testGroup', 'fakeGuard'));

        $this->assertFalse($this->testUser->hasAllGroups(['testGroup', 'second group']));
        $this->assertFalse($this->testUser->hasAllGroups(['testGroup', 'second group'], 'web'));

        $this->testUser->assignGroup('second group');

        $this->assertTrue($this->testUser->hasAllGroups(['testGroup', 'second group']));
        $this->assertTrue($this->testUser->hasAllGroups(['testGroup', 'second group'], 'web'));
        $this->assertFalse($this->testUser->hasAllGroups(['testGroup', 'second group'], 'fakeGuard'));
    }

    /** @test */
    public function it_can_determine_that_a_user_does_not_have_a_group_from_another_guard()
    {
        $this->assertFalse($this->testUser->hasGroup('testAdminGroup'));

        $this->assertFalse($this->testUser->hasGroup($this->testAdminGroup));

        $this->testUser->assignGroup('testGroup');

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup', 'testAdminGroup']));

        $this->assertFalse($this->testUser->hasAnyGroup('testAdminGroup', $this->testAdminGroup));
    }

    /** @test */
    public function it_can_check_against_any_multiple_groups_using_multiple_arguments()
    {
        $this->testUser->assignGroup('testGroup');

        $this->assertTrue($this->testUser->hasAnyGroup($this->testAdminGroup, ['testGroup'], 'This Group Does Not Even Exist'));
    }

    /** @test */
    public function it_returns_false_instead_of_an_exception_when_checking_against_any_undefined_groups_using_multiple_arguments()
    {
        $this->assertFalse($this->testUser->hasAnyGroup('This Group Does Not Even Exist', $this->testAdminGroup));
    }

    /** @test */
    public function it_can_retrieve_group_names()
    {
        $this->testUser->assignGroup('testGroup', 'testGroup2');

        $this->assertEquals(
            collect(['testGroup', 'testGroup2']),
            $this->testUser->getGroupNames()
        );
    }

    /** @test */
    public function it_does_not_detach_groups_when_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->assignGroup('testGroup');
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasGroup('testGroup'));
    }
}
