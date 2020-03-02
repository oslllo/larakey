<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Padlock\Cache;

trait RefreshesLarakeyCache
{
    public static function bootRefreshesLarakeyCache()
    {
        static::saved(function () {
            app(Cache::class)->forgetCachedPermissions();
            app(Cache::class)->forgetCachedRoles();
        });

        static::deleted(function () {
            app(Cache::class)->forgetCachedPermissions();
            app(Cache::class)->forgetCachedRoles();
        });
    }
}
