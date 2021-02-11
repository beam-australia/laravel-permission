<?php

namespace Spatie\Permission\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    private $requiredGroups = [];

    private $requiredPermissions = [];

    public static function forGroups(array $groups): self
    {
        $message = 'User does not have the right groups.';

        if (config('permission.display_permission_in_exception')) {
            $permStr = implode(', ', $groups);
            $message = 'User does not have the right groups. Necessary groups are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredGroups = $groups;

        return $exception;
    }

    public static function forPermissions(array $permissions): self
    {
        $message = 'User does not have the right permissions.';

        if (config('permission.display_permission_in_exception')) {
            $permStr = implode(', ', $permissions);
            $message = 'User does not have the right permissions. Necessary permissions are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $permissions;

        return $exception;
    }

    public static function forGroupsOrPermissions(array $groupsOrPermissions): self
    {
        $message = 'User does not have any of the necessary access rights.';

        if (config('permission.display_permission_in_exception') && config('permission.display_group_in_exception')) {
            $permStr = implode(', ', $groupsOrPermissions);
            $message = 'User does not have the right permissions. Necessary permissions are '.$permStr;
        }

        $exception = new static(403, $message, null, []);
        $exception->requiredPermissions = $groupsOrPermissions;

        return $exception;
    }

    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }

    public function getRequiredGroups(): array
    {
        return $this->requiredGroups;
    }

    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
