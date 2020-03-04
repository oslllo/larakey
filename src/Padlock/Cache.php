<?php

namespace Ghustavh97\Larakey\Padlock;

use Ghustavh97\Larakey\Larakey;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate;
use Ghustavh97\Larakey\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Ghustavh97\Larakey\Contracts\HasPermission;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;
use Ghustavh97\Larakey\Padlock\Config;

class Cache
{
    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cacheManager;

    /** @var \Illuminate\Support\Collection */
    protected $permissions;

    /** @var \Illuminate\Support\Collection */
    protected $roles;

    /** @var DateInterval|int */
    public static $cacheExpirationTime;

    /** @var string */
    public static $cachePermissionKey;

    /** @var string */
    public static $cacheRoleKey;

    /** @var string */
    public static $cacheModelKey;

    /**
     * LarakeyRegistrar constructor.
     *
     * @param \Illuminate\Cache\CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    protected function initializeCache()
    {
        self::$cacheExpirationTime = config(Config::$cacheExpirationTime);

        self::$cachePermissionKey = config(Config::$cachePermissionKey);

        self::$cacheRoleKey = config(Config::$cacheRoleKey);
        
        self::$cacheModelKey = config(Config::$cacheModelKey);

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): \Illuminate\Contracts\Cache\Repository
    {
        /**
         * The'default' fallback here is from the permission.php config file,
         * where 'default' means to use config(cache.default)
         */
        $cacheDriver = config(Config::$cacheStore, 'default');

        // when 'default' is specified, no action is required since we already have the default instance
        if ($cacheDriver === 'default') {
            return $this->cacheManager->store();
        }

        // if an undefined cache store is specified, fallback to 'array' which is Laravel's closest equiv to 'none'
        if (! \array_key_exists($cacheDriver, config('cache.stores'))) {
            $cacheDriver = 'array';
        }

        return $this->cacheManager->store($cacheDriver);
    }

    /**
     * Flush the cache permissions.
     */
    public function forgetCachedPermissions($reload = false)
    {
        $this->permissions = null;

        $forgotten = $this->cache->forget(self::$cachePermissionKey);

        if ($reload) {
            $this->getCachedPermissions();
        }

        return $forgotten;
    }

    /**
     * Get the permissions based on the passed params.
     *
     * @param array $params
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCachedPermissions(array $params = []): Collection //!GetcachedPermissions
    {
        if ($this->permissions === null) {
            $this->permissions = $this->cache->remember(
                self::$cachePermissionKey,
                self::$cacheExpirationTime,
                function () {
                    return app(Larakey::class)->getPermissionClass()
                    ->with('roles')
                    ->get();
                }
            );
        }

        $permissions = clone $this->permissions;

        foreach ($params as $attr => $value) {
            $permissions = $permissions->where($attr, $value);
        }

        return $permissions;
    }

    /**
     * Flush the cache roles.
     */
    public function forgetCachedRoles($reload = false)
    {
        $this->roles = null;

        $forgotten = $this->cache->forget(self::$cacheRoleKey);

        if ($reload) {
            $this->getCachedRoles();
        }

        return $forgotten;
    }

    public function getCachedRoles(array $params = []): Collection
    {
        if ($this->roles === null) {
            $this->roles = $this->cache->remember(self::$cacheRoleKey, self::$cacheExpirationTime, function () {
                return app(Larakey::class)->getRoleClass()
                    ->with('permissions')
                    ->get();
            });
        }

        $roles = clone $this->roles;

        foreach ($params as $attr => $value) {
            $roles = $roles->where($attr, $value);
        }

        return $roles;
    }

    public function loadCache()
    {
        $this->getCachedPermissions();
        $this->getCachedRoles();
    }

    public function flushCache()
    {
        return $this->forgetCachedPermissions() && $this->forgetCachedRoles();
    }
    
    /**
     * Get the instance of the Cache Store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getCacheStore(): \Illuminate\Contracts\Cache\Store
    {
        return $this->cache->getStore();
    }
}
