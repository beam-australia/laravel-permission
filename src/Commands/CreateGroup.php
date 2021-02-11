<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Group as GroupContract;

class CreateGroup extends Command
{
    protected $signature = 'permission:create-group
        {name : The name of the group}
        {guard? : The name of the guard}
        {permissions? : A list of permissions to assign to the group, separated by | }';

    protected $description = 'Create a group';

    public function handle()
    {
        $groupClass = app(GroupContract::class);

        $group = $groupClass::findOrCreate($this->argument('name'), $this->argument('guard'));

        $group->givePermissionTo($this->makePermissions($this->argument('permissions')));

        $this->info("Group `{$group->name}` created");
    }

    /**
     * @param array|null|string $string
     */
    protected function makePermissions($string = null)
    {
        if (empty($string)) {
            return;
        }

        $permissionClass = app(PermissionContract::class);

        $permissions = explode('|', $string);

        $models = [];

        foreach ($permissions as $permission) {
            $models[] = $permissionClass::findOrCreate(trim($permission), $this->argument('guard'));
        }

        return collect($models);
    }
}
