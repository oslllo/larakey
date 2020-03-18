<?php

namespace Oslllo\Larakey\Traits;

use Oslllo\Larakey\Padlock\Cache;

trait RefreshesLarakeyCache
{
    /**
     * Boots RefreshesLarakeyCache trait.
     *
     * @return void
     */
    public static function bootRefreshesLarakeyCache(): void
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
