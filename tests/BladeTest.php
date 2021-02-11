<?php

namespace Spatie\Permission\Test;

use Artisan;
use Spatie\Permission\Contracts\Group;

class BladeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $groupModel = app(Group::class);

        $groupModel->create(['name' => 'member']);
        $groupModel->create(['name' => 'writer']);
        $groupModel->create(['name' => 'intern']);
        $groupModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
        $groupModel->create(['name' => 'moderator', 'guard_name' => 'admin']);
    }

    /** @test */
    public function all_blade_directives_will_evaluate_false_when_there_is_nobody_logged_in()
    {
        $permission = 'edit-articles';
        $group = 'writer';
        $groups = [$group];
        $elsegroup = 'na';

        $this->assertEquals('does not have permission', $this->renderView('can', ['permission' => $permission]));
        $this->assertEquals('does not have group', $this->renderView('group', compact('group', 'elsegroup')));
        $this->assertEquals('does not have group', $this->renderView('hasGroup', compact('group', 'elsegroup')));
        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', $groups));
        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', ['groups' => implode('|', $groups)]));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', $groups));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', ['groups' => implode('|', $groups)]));
    }

    /** @test */
    public function all_blade_directives_will_evaluate_false_when_somebody_without_groups_or_permissions_is_logged_in()
    {
        $permission = 'edit-articles';
        $group = 'writer';
        $groups = 'writer';
        $elsegroup = 'na';

        auth()->setUser($this->testUser);

        $this->assertEquals('does not have permission', $this->renderView('can', ['permission' => $permission]));
        $this->assertEquals('does not have group', $this->renderView('group', compact('group', 'elsegroup')));
        $this->assertEquals('does not have group', $this->renderView('hasGroup', compact('group', 'elsegroup')));
        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', compact('groups')));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', compact('groups')));
    }

    /** @test */
    public function all_blade_directives_will_evaluate_false_when_somebody_with_another_guard_is_logged_in()
    {
        $permission = 'edit-articles';
        $group = 'writer';
        $groups = 'writer';
        $elsegroup = 'na';

        auth('admin')->setUser($this->testAdmin);

        $this->assertEquals('does not have permission', $this->renderView('can', compact('permission')));
        $this->assertEquals('does not have group', $this->renderView('group', compact('group', 'elsegroup')));
        $this->assertEquals('does not have group', $this->renderView('hasGroup', compact('group', 'elsegroup')));
        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', compact('groups')));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', compact('groups')));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', compact('groups')));
    }

    /** @test */
    public function the_can_directive_will_evaluate_true_when_the_logged_in_user_has_the_permission()
    {
        $user = $this->getWriter();

        $user->givePermissionTo('edit-articles');

        auth()->setUser($user);

        $this->assertEquals('has permission', $this->renderView('can', ['permission' => 'edit-articles']));
    }

    /** @test */
    public function the_group_directive_will_evaluate_true_when_the_logged_in_user_has_the_group()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('has group', $this->renderView('group', ['group' => 'writer', 'elsegroup' => 'na']));
    }

    /** @test */
    public function the_elsegroup_directive_will_evaluate_true_when_the_logged_in_user_has_the_group()
    {
        auth()->setUser($this->getMember());

        $this->assertEquals('has else group', $this->renderView('group', ['group' => 'writer', 'elsegroup' => 'member']));
    }

    /** @test */
    public function the_group_directive_will_evaluate_true_when_the_logged_in_user_has_the_group_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('has group for guard', $this->renderView('guardGroup', ['group' => 'super-admin', 'guard' => 'admin']));
    }

    /** @test */
    public function the_hasgroup_directive_will_evaluate_true_when_the_logged_in_user_has_the_group()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('has group', $this->renderView('hasGroup', ['group' => 'writer']));
    }

    /** @test */
    public function the_hasgroup_directive_will_evaluate_true_when_the_logged_in_user_has_the_group_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('has group', $this->renderView('guardHasGroup', ['group' => 'super-admin', 'guard' => 'admin']));
    }

    /** @test */
    public function the_unlessgroup_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_the_group()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('does not have group', $this->renderView('unlessgroup', ['group' => 'another']));
    }

    /** @test */
    public function the_unlessgroup_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_the_group_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does not have group', $this->renderView('guardunlessgroup', ['group' => 'another', 'guard' => 'admin']));
        $this->assertEquals('does not have group', $this->renderView('guardunlessgroup', ['group' => 'super-admin', 'guard' => 'web']));
    }

    /** @test */
    public function the_hasanygroup_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_any_of_the_required_groups()
    {
        $groups = ['writer', 'intern'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', compact('groups')));
        $this->assertEquals('does not have any of the given groups', $this->renderView('hasAnyGroup', ['groups' => implode('|', $groups)]));
    }

    /** @test */
    public function the_hasanygroup_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_groups()
    {
        $groups = ['member', 'writer', 'intern'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does have some of the groups', $this->renderView('hasAnyGroup', compact('groups')));
        $this->assertEquals('does have some of the groups', $this->renderView('hasAnyGroup', ['groups' => implode('|', $groups)]));
    }

    /** @test */
    public function the_hasanygroup_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_groups_for_the_given_guard()
    {
        $groups = ['super-admin', 'moderator'];
        $guard = 'admin';

        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does have some of the groups', $this->renderView('guardHasAnyGroup', compact('groups', 'guard')));
    }

    /** @test */
    public function the_hasanygroup_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_groups_in_pipe()
    {
        $guard = 'admin';

        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does have some of the groups', $this->renderView('guardHasAnyGroupPipe', compact('guard')));
    }

    /** @test */
    public function the_hasanygroup_directive_will_evaluate_false_when_the_logged_in_user_doesnt_have_some_of_the_required_groups_in_pipe()
    {
        $guard = '';

        auth('admin')->setUser($this->getMember());

        $this->assertEquals('does not have any of the given groups', $this->renderView('guardHasAnyGroupPipe', compact('guard')));
    }

    /** @test */
    public function the_hasallgroups_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_all_required_groups()
    {
        $groups = ['member', 'writer'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', compact('groups')));
        $this->assertEquals('does not have all of the given groups', $this->renderView('hasAllGroups', ['groups' => implode('|', $groups)]));
    }

    /** @test */
    public function the_hasallgroups_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_groups()
    {
        $groups = ['member', 'writer'];

        $user = $this->getMember();

        $user->assignGroup('writer');

        auth()->setUser($user);

        $this->assertEquals('does have all of the given groups', $this->renderView('hasAllGroups', compact('groups')));
        $this->assertEquals('does have all of the given groups', $this->renderView('hasAllGroups', ['groups' => implode('|', $groups)]));
    }

    /** @test */
    public function the_hasallgroups_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_groups_for_the_given_guard()
    {
        $groups = ['super-admin', 'moderator'];
        $guard = 'admin';

        $admin = $this->getSuperAdmin();

        $admin->assignGroup('moderator');

        auth('admin')->setUser($admin);

        $this->assertEquals('does have all of the given groups', $this->renderView('guardHasAllGroups', compact('groups', 'guard')));
    }

    /** @test */
    public function the_hasallgroups_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_groups_in_pipe()
    {
        $guard = 'admin';

        $admin = $this->getSuperAdmin();

        $admin->assignGroup('moderator');

        auth('admin')->setUser($admin);

        $this->assertEquals('does have all of the given groups', $this->renderView('guardHasAllGroupsPipe', compact('guard')));
    }

    /** @test */
    public function the_hasallgroups_directive_will_evaluate_false_when_the_logged_in_user_doesnt_have_all_required_groups_in_pipe()
    {
        $guard = '';
        $user = $this->getMember();

        $user->assignGroup('writer');

        auth()->setUser($user);

        $this->assertEquals('does not have all of the given groups', $this->renderView('guardHasAllGroupsPipe', compact('guard')));
    }

    protected function getWriter()
    {
        $this->testUser->assignGroup('writer');

        return $this->testUser;
    }

    protected function getMember()
    {
        $this->testUser->assignGroup('member');

        return $this->testUser;
    }

    protected function getSuperAdmin()
    {
        $this->testAdmin->assignGroup('super-admin');

        return $this->testAdmin;
    }

    protected function renderView($view, $parameters)
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
