<?php

namespace Ghustavh97\Larakey\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Ghustavh97\Larakey\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    /**
     * Middleware handle.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|array $permission
     *
     * @return mixed
     *
     * @throws \Ghustavh97\Larakey\Exceptions\UnauthorizedException
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (app('auth')->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (app('auth')->user()->can($permission)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forPermissions($permissions);
    }
}
