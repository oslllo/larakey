<?php

namespace Ghustavh97\Guardian;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Ghustavh97\Guardian\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate;
use Ghustavh97\Guardian\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Ghustavh97\Guardian\Contracts\PermissionPivot;
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
    protected $permissionPivotClass;

    /** @var string */
    protected $roleClass;

    /** @var \Illuminate\Support\Collection */
    protected $permissions;

    /** @var DateInterval|int */
    public static $cacheExpirationTime;

    /** @var string */
    public static $cacheKey;

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
        $this->permissionPivotClass = config('guardian.models.permission_pivot');
        $this->roleClass = config('guardian.models.role');

        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    protected function initializeCache()
    {
        self::$cacheExpirationTime = config('guardian.cache.expiration_time', config('guardian.cache_expiration_time'));

        self::$cacheKey = config('guardian.cache.key');
        self::$cacheModelKey = config('guardian.cache.model_key');

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): \Illuminate\Contracts\Cache\Repository
    {
        // the 'default' fallback here is from the permission.php config file, where 'default' means to use config(cache.default)
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
     * Flush the cache.
     */
    public function forgetCachedPermissions()
    {
        $this->permissions = null;

        return $this->cache->forget(self::$cacheKey);
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
            $this->permissions = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, function () {
                return $this->getPermissionClass()
                    ->with('roles')
                    ->get();
            });
        }

        $permissions = clone $this->permissions;

        foreach ($params as $attr => $value) {
            $permissions = $permissions->where($attr, $value);
        }

        return $permissions;
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
     * Get an instance of the permissionPivot class.
     *
     * @return \Ghustavh97\Guardian\Contracts\PermissionPivot
     */
    public function getPermissionPivotClass(): PermissionPivot
    {
        return app($this->permissionPivotClass);
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
     * Get the instance of the Cache Store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getCacheStore(): \Illuminate\Contracts\Cache\Store
    {
        return $this->cache->getStore();
    }
}
