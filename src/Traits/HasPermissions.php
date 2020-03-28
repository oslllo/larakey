<?php

namespace Oslllo\Larakey\Traits;

use Oslllo\Larakey\Guard;
use Illuminate\Support\Collection;
use Oslllo\Larakey\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Oslllo\Larakey\Contracts\Permission;
use Oslllo\Larakey\Contracts\Locksmith;
use Oslllo\Larakey\Models\HasPermission;
use Oslllo\Larakey\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Oslllo\Larakey\Exceptions\StrictPermission;
use Oslllo\Larakey\Exceptions\PermissionDoesNotExist;
use Oslllo\Larakey\Exceptions\PermissionNotAssigned;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;
use Oslllo\Larakey\Larakey;
use Oslllo\Larakey\Padlock\Cache;
use Oslllo\Larakey\Padlock\Config;
use Oslllo\Larakey\Padlock\Key;
use Oslllo\Larakey\Traits\LarakeyHelpers;

trait HasPermissions
{
    use LarakeyHelpers;

    /**
     * Permission class instance.
     *
     * @var \Oslllo\Larakey\Contracts\Permission|Oslllo\Larakey\Models\Permission
     */
    private $permissionClass;

    /**
     * Boot HasPermission trait.
     *
     * @return void
     */
    public static function bootHasPermissions(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    /**
     * Get permission class instance.
     *
     * @return \Oslllo\Larakey\Contracts\Permission|Oslllo\Larakey\Models\Permission
     */
    public function getPermissionClass(): Permission
    {
        if (! isset($this->permissionClass)) {
            $this->permissionClass = app(Larakey::class)->getPermissionClass();
        }

        return $this->permissionClass;
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('larakey.models.permission'),
            'model',
            config('larakey.models.permission_pivot'),
            config('larakey.column_names.model_morph_key'),
            'permission_id'
        )->withPivot(['to_type', 'to_id'])->withTimestamps();
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Oslllo\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission(Builder $query, ...$arguments): Builder
    {
        $combination = $this->combination($arguments);

        extract($combination->get(['permissions', 'model']));

        $permissionKey = $this->getPermissionKey($model);

        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        $query = $query->where(function ($query) use ($permissions, $rolesWithPermissions, $permissionKey) {
            $query->whereHas('permissions', function ($query) use ($permissions, $permissionKey) {
                $query->where(config(Config::$modelHasPermissionTableName).'.to_id', $permissionKey->to_id)
                    ->where(config(Config::$modelHasPermissionTableName).'.to_type', $permissionKey->to_type)
                    ->whereIn(config(Config::$permissionsTableName).'.id', \array_column($permissions, 'id'));
            });
            
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions, $permissions, $permissionKey) {
                    $query->where(function ($query) use ($rolesWithPermissions, $permissions, $permissionKey) {
                        $query->whereIn(config(Config::$rolesTableName).'.id', \array_column($rolesWithPermissions, 'id'))
                        ->whereHas('permissions', function ($query) use ($permissions, $permissionKey) {
                            $query->where(config(Config::$modelHasPermissionTableName).'.to_id', $permissionKey->to_id)
                                ->where(config(Config::$modelHasPermissionTableName).'.to_type', $permissionKey->to_type);
                        });
                    });
                });
            }
        });

        return $query;
    }

    /**
     * @param string|array|\Oslllo\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return array
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return array_map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission;
            }

            return $this->getPermissionClass()->findByName($permission, $this->getDefaultGuardName());
        }, $permissions);
    }

    // get only one permission
    private function getPermission($permission, $guard = null): Permission
    {
        $permissionClass = $this->getPermissionClass();
        $guard = $this->getGuard($guard);

        if (is_array($permission)) {
            $permission = $permission[0];
        }

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $guard);
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $guard);
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        return $permission;
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param array $arguments
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo(...$arguments): bool
    {
        return call_user_func_array(array($this, 'hasDirectPermission'), func_get_args())
            || call_user_func_array(array($this, 'hasPermissionViaRole'), func_get_args());
    }

    /**
     * An alias to hasPermissionTo(), but avoids throwing an exception.
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function checkPermissionTo(...$arguments): bool
    {
        try {
            return call_user_func_array(array($this, 'hasPermissionTo'), func_get_args());
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $permissions = collect($permissions)->larakeyMapInto('array');

        foreach ($permissions as $permission) {
            if (call_user_func_array(array($this, 'checkPermissionTo'), $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$arguments
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $permissions = collect($permissions)->larakeyMapInto('array');

        foreach ($permissions as $permission) {
            if (! call_user_func_array(array($this, 'checkPermissionTo'), $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get permission role instance.
     *
     * @param \Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Models\Permission $permission
     * @return \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
     */
    public function getPermissionRole(Permission $permission): Role
    {
        foreach ($this->roles as $modelRole) {
            foreach ($permission->roles as $permissionRole) {
                if ($modelRole->id === $permissionRole->id) {
                    return $modelRole;
                }
            }
        }

        return app(Role::class);
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param array $arguments
     *
     * @return bool
     */
    protected function hasPermissionViaRole(...$arguments): bool
    {
        $combination = $this->combination($arguments);

        extract($combination->get(['permissions', 'guard']));

        $permission = $this->getPermission($permissions, $guard);

        if ($this instanceof Role) {
            $role = $this;
        }

        if (! isset($role)) {
            if (! $role = $this->hasRole($permission->roles, $guard, true)) {
                return false;
            }
        }

        // Get same role but with permissions
        $role = $this->getRole($role->id, $role->guard_name);

        return call_user_func_array(array($role, 'hasDirectPermission'), func_get_args());
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param array $arguments
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission(...$arguments): bool
    {
        $combination = $this->combination($arguments);

        extract($combination->get(['permissions', 'model', 'guard']));

        if (! $permissions) {
            throw new PermissionDoesNotExist;
        }

        return collect($permissions)->every(function ($item) use ($guard, $model) {

            $permission = $this->getPermission($item, $guard);

            if (! $this->getGuardNames()->contains($permission->guard_name)) {
                throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
            }

            $key = $this->getPermissionKey($model);

            return $key->unlocks($this->getModel(), $permission) == true;
        });
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        /** @var Collection $permissions */
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    /**
     * Converts permissions into a collection
     *
     * @param array|string|\Illuminate\Support\Collection $permissions
     * @return Collection
     */
    public function permissionsToCollection($permissions = []): Collection
    {
        if ($permissions instanceof Collection) {
            return $permissions;
        } elseif (is_string($permissions) || $permissions instanceof Permission) {
            $permissions = [$permissions];
        }

        return collect($permissions);
    }

    /**
     * Grant the given permissions to a model.
     *
     * @param array $permissions
     * @return $this
     */
    public function giveMultiplePermissionsTo(array $permissions)
    {
        $permissions = collect($permissions)->larakeyMapInto('array');

        foreach ($permissions as $permission) {
            call_user_func_array(array($this, 'givePermissionTo'), $permission);
        }

        return $this;
    }

    /**
     * Grant the given permission to a model.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function givePermissionTo(...$arguments)
    {
        $combination = $this->combination($arguments);

        extract($combination->get(['permissions', 'model']));

        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->map->id
            ->all();

        if (! $model && config(Config::$strictPermissionAssignment)) {
            throw StrictPermission::assignment();
        }

        $permissions = collect($permissions)->map(function ($permissionId, $key) use ($model) {
            $permissionKey = $this->getPermissionKey($model);

            if ($this->permissions()
                    ->where('id', $permissionId)
                    ->wherePivot('to_id', $permissionKey->to_id)
                    ->wherePivot('to_type', $permissionKey->to_type)
                    ->first()) {
                return false;
            }

            return array($permissionId => $permissionKey->getPivot());
        })
        ->reject(function ($value) {
            return $value === false;
        })
        ->all();
        
        $temp = [];

        foreach ($permissions as $permission) {
            foreach (array_keys($permission) as $permissionKey) {
                $temp[$permissionKey] = $permission[$permissionKey];
            }
        }

        $permissions = $temp;

        $self = $this->getModel();
        
        if ($self->exists) {
            $this->permissions()->attach($permissions);
            $self->load('permissions');
        } else {
            $class = \get_class($self);

            $class::saved(
                function ($object) use ($permissions, $self) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $self) {
                        return;
                    }
                    $object->permissions()->attach($permissions);
                    $object->load('permissions');
                    $modelLastFiredOn = $object;
                }
            );
        }

        $this->forgetCachedPermissions();

        if ($this instanceof Role) {
            app(Cache::class)->forgetCachedRoles();
        }

        return $this;
    }

    /**
     * Returns all permissions directly coupled to the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|\Oslllo\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(array $permissions)
    {
        $this->permissions()->detach();

        if (! count($permissions)) {
            $this->load('permissions');
        }

        return $this->giveMultiplePermissionsTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function revokePermissionTo(...$arguments)
    {
        $combination = $this->combination($arguments);

        extract($combination->get(['permissions', 'model', 'recursive', 'guard']));

        if (! $model && config(Config::$strictPermissionRevoke)) {
            throw StrictPermission::revoke();
        }

        $permissionKey = $this->getPermissionKey($model);

        collect($permissions)->each(function ($permission) use ($recursive, $permissionKey, $guard) {

            if (! $permission instanceof Permission) {
                $permission = $this->getPermission($permission, $guard);
            }

            $detach = $permission->id;

            if ($permissionKey->hasFullAccess()) {
                $permission = $this->permissions()
                                   ->where('id', $permission->id);

                if (! $recursive) {
                    $permission = $permission->wherePivot('to_id', Larakey::WILDCARD_TOKEN)
                                             ->wherePivot('to_type', Larakey::WILDCARD_TOKEN);
                }
            } elseif ($permissionKey->hasClassAccess()) {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_type', $permissionKey->to_type);

                if (! $recursive) {
                    $permission = $permission->wherePivot('to_id', Larakey::WILDCARD_TOKEN);
                }
            } else {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_id', $permissionKey->to_id)
                                ->wherePivot('to_type', $permissionKey->to_type);
            }

            if (! $permission->first()) {
                throw new PermissionNotAssigned;
            }

            $permission->detach($detach);
        });

        $this->forgetCachedPermissions();

        if (\method_exists($this, 'forgetCachedRoles')) {
            $this->forgetCachedRoles();
        }

        $this->load('permissions');

        return $this;
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('name');
    }

    /**
     * @param string|array|\Oslllo\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     * @param string|null $guard
     *
     * @return \Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Contracts\Permission[]|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permission, $guard = null)
    {
        $permissionClass = $this->getPermissionClass();
        $guard = $this->getGuard($guard);

        if ($permission instanceof Permission) {
            return $permission;
        }

        if (is_numeric($permission)) {
            return $permissionClass->findById($permission, $guard);
        }

        if (is_string($permission)) {
            return $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_array($permission)) {
            return app(Permission::class)
            ->whereIn('name', $permission)
            ->whereIn('guard_name', $guard)
            ->get();
        }
    }

    /**
     * @param \Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Contracts\Role $roleOrPermission
     *
     * @throws \Oslllo\Larakey\Exceptions\GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard($roleOrPermission)
    {
        if (! $this->getGuardNames()->contains($roleOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create($roleOrPermission->guard_name, $this->getGuardNames());
        }
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions($reload = false)
    {
        app(Cache::class)->forgetCachedPermissions($reload);
    }
}
