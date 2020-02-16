<?php

namespace Ghustavh97\Guardian;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Ghustavh97\Guardian\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate;
use Ghustavh97\Guardian\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Ghustavh97\Guardian\Contracts\ModelHasPermission;
use Ghustavh97\Guardian\Exceptions\ClassDoesNotExist;

class GuardianRegistrar
{
    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cacheManager;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $ModelHasPermissionClass;

    /** @var string */
    protected $roleClass;

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
     * GuardianRegistrar constructor.
     *
     * @param \Illuminate\Cache\CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->permissionClass = config('guardian.models.permission');
        $this->ModelHasPermissionClass = config('guardian.models.permission_pivot');
        $this->roleClass = config('guardian.models.role');

        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    protected function initializeCache()
    {
        self::$cacheExpirationTime = config('guardian.cache.expiration_time', config('guardian.cache_expiration_time'));

        self::$cachePermissionKey = config('guardian.cache.permission_key');

        self::$cacheRoleKey = config('guardian.cache.role_key');
        
        self::$cacheModelKey = config('guardian.cache.model_key');

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): \Illuminate\Contracts\Cache\Repository
    {
        /**
         * The'default' fallback here is from the permission.php config file,
         * where 'default' means to use config(cache.default)
         */
        $cacheDriver = config('guardian.cache.store', 'default');

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
     * Register the permission check method on the gate.
     * We resolve the Gate fresh here, for benefit of long-running instances.
     *
     * @return bool
     */
    public function registerPermissions(): bool
    {
        app(Gate::class)->before(function (Authorizable $user, string $ability, $attributes = []) {
            $guardName = array_key_exists(1, $attributes) ? $attributes[1] : null;
            if (method_exists($user, 'checkPermissionTo')) {
                return $user->checkPermissionTo($ability, $attributes, $guardName) ?: null;
            }
        });

        return true;
    }

    /**
     * Flush the cache permissions.
     */
    public function forgetCachedPermissions($reload = false)
    {
        $this->permissions = null;

        $forgotten = $this->cache->forget(self::$cachePermissionKey);

        if ($reload) {
            $this->getPermissions();
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
    public function getPermissions(array $params = []): Collection
    {
        if ($this->permissions === null) {
            $this->permissions = $this->cache->remember(
                self::$cachePermissionKey,
                self::$cacheExpirationTime,
                function () {
                    return $this->getPermissionClass()
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
            $this->getRoles();
        }

        return $forgotten;
    }

    public function getRoles(array $params = []): Collection
    {
        if ($this->roles === null) {
            $this->roles = $this->cache->remember(self::$cacheRoleKey, self::$cacheExpirationTime, function () {
                return $this->getRoleClass()
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
        $this->getPermissions();
        $this->getRoles();
    }

    public function flushCache()
    {
        return $this->forgetCachedPermissions() && $this->forgetCachedRoles();
    }

    /**
     * Get an instance of the permission class.
     *
     * @return \Ghustavh97\Guardian\Contracts\Permission
     */
    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }

    /**
     * Get an instance of the role class.
     *
     * @return \Ghustavh97\Guardian\Contracts\Role
     */
    public function getRoleClass(): Role
    {
        return app($this->roleClass);
    }

    /**
     * Get an instance of the ModelHasPermission class.
     *
     * @return \Ghustavh97\Guardian\Contracts\ModelHasPermission
     */
    public function getModelHasPermissionClass(): ModelHasPermission
    {
        return app($this->ModelHasPermissionClass);
    }

    public function getModelFromAttributes($attributes = [])
    {
        if (count($attributes)) {
            $model = $attributes[0];

            if (is_string($model)) {
                if (! class_exists($model)) {
                    throw ClassDoesNotExist::check($model);
                }
                return new $model;
            }

            if ($model instanceof Model) {
                return $model;
            }
        }

        return null;
    }

    public function setPermissionClass($permissionClass)
    {
        $this->permissionClass = $permissionClass;

        return $this;
    }

    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;

        return $this;
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
