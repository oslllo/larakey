<?php

namespace Ghustavh97\Guardian\Traits;

use Ghustavh97\Guardian\GuardianRegistrar;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::saved(function () {
            app(GuardianRegistrar::class)->forgetCachedPermissions();
            app(GuardianRegistrar::class)->forgetCachedRoles();
        });

        static::deleted(function () {
            app(GuardianRegistrar::class)->forgetCachedPermissions();
            app(GuardianRegistrar::class)->forgetCachedRoles();
        });
    }
}
