<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class GroupMiddleware
{
    public function handle($request, Closure $next, $group, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $groups = is_array($group)
            ? $group
            : explode('|', $group);

        if (! Auth::guard($guard)->user()->hasAnyGroup($groups)) {
            throw UnauthorizedException::forGroups($groups);
        }

        return $next($request);
    }
}
