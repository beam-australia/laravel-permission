<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class GroupOrPermissionMiddleware
{
    public function handle($request, Closure $next, $groupOrPermission, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $groupsOrPermissions = is_array($groupOrPermission)
            ? $groupOrPermission
            : explode('|', $groupOrPermission);

        if (! Auth::guard($guard)->user()->hasAnyGroup($groupsOrPermissions) && ! Auth::guard($guard)->user()->hasAnyPermission($groupsOrPermissions)) {
            throw UnauthorizedException::forGroupsOrPermissions($groupsOrPermissions);
        }

        return $next($request);
    }
}
