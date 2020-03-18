<?php

namespace Oslllo\Larakey\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Oslllo\Larakey\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    /**
     * Middleware handle.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|array $roleOrPermission
     *
     * @return mixed
     *
     * @throws \Oslllo\Larakey\Exceptions\UnauthorizedException
     */
    public function handle(Request $request, Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        if (! Auth::user()->hasAnyRole($rolesOrPermissions) && ! Auth::user()->hasAnyPermission($rolesOrPermissions)) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }
}
