<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\LarakeyRegistrar;

trait RefreshesLarakeyCache
{
    public static function bootRefreshesLarakeyCache()
    {
        static::saved(function () {
            app(LarakeyRegistrar::class)->forgetCachedPermissions();
            app(LarakeyRegistrar::class)->forgetCachedRoles();
        });

        static::deleted(function () {
            app(LarakeyRegistrar::class)->forgetCachedPermissions();
            app(LarakeyRegistrar::class)->forgetCachedRoles();
        });
    }
}
