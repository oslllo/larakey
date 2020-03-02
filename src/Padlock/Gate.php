<?php

namespace Ghustavh97\Larakey\Padlock;

use Illuminate\Contracts\Auth\Access\Gate as IlluminateGate;
use Illuminate\Contracts\Auth\Access\Authorizable;

class Gate
{
    /**
     * Register the permission check method on the gate.
     * We resolve the Gate fresh here, for benefit of long-running instances.
     *
     * @return bool
     */
    public function registerPermissions(): bool
    {
        app(IlluminateGate::class)->before(function (Authorizable $user, $permission, $arguments = []) {

            if (method_exists($user, 'checkPermissionTo')) {
                $arguments = array_merge([$permission], $arguments);

                return call_user_func_array(array($user, 'checkPermissionTo'), $arguments) ?: null;
            }
        });

        return true;
    }
}
