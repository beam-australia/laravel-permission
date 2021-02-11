<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Group as GroupContract;

class Show extends Command
{
    protected $signature = 'permission:show
            {guard? : The name of the guard}
            {style? : The display style (default|borderless|compact|box)}';

    protected $description = 'Show a table of groups and permissions per guard';

    public function handle()
    {
        $permissionClass = app(PermissionContract::class);
        $groupClass = app(GroupContract::class);

        $style = $this->argument('style') ?? 'default';
        $guard = $this->argument('guard');

        if ($guard) {
            $guards = Collection::make([$guard]);
        } else {
            $guards = $permissionClass::pluck('guard_name')->merge($groupClass::pluck('guard_name'))->unique();
        }

        foreach ($guards as $guard) {
            $this->info("Guard: $guard");

            $groups = $groupClass::whereGuardName($guard)->orderBy('name')->get()->mapWithKeys(function ($group) {
                return [$group->name => $group->permissions->pluck('name')];
            });

            $permissions = $permissionClass::whereGuardName($guard)->orderBy('name')->pluck('name');

            $body = $permissions->map(function ($permission) use ($groups) {
                return $groups->map(function (Collection $group_permissions) use ($permission) {
                    return $group_permissions->contains($permission) ? ' ✔' : ' ·';
                })->prepend($permission);
            });

            $this->table(
                $groups->keys()->prepend('')->toArray(),
                $body->toArray(),
                $style
            );
        }
    }
}
