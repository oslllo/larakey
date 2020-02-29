<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Guard;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Ghustavh97\Larakey\Contracts\Permission;
use Ghustavh97\Larakey\Models\ModelHasPermission;
use Ghustavh97\Larakey\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ghustavh97\Larakey\Exceptions\StrictPermission;
use Ghustavh97\Larakey\Exceptions\PermissionDoesNotExist;
use Ghustavh97\Larakey\Exceptions\PermissionNotAssigned;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

use Ghustavh97\Larakey\Larakey;

use Ghustavh97\Larakey\Padlock\Cache;
use Ghustavh97\Larakey\Padlock\Config;
use Ghustavh97\Larakey\Padlock\Key;

trait HasLarakeyPermissions
{
    use LarakeyTraitHelpers;

    private $permissionClass;

    public static function bootHasLarakeyPermissions()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    public function getPermissionClass()
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
     * @param string|array|\Ghustavh97\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission(Builder $query, $permissions, $to = null): Builder
    {
        $permissionScope = $this->locksmith()->getKey($to);

        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        $query = $query->where(function ($query) use ($permissions, $rolesWithPermissions, $permissionScope) {
            $query->whereHas('permissions', function ($query) use ($permissions, $permissionScope) {
                $query->where(config(Config::$modelHasPermissionTableName).'.to_id', $permissionScope->to_id)
                    ->where(config(Config::$modelHasPermissionTableName).'.to_type', $permissionScope->to_type)
                    ->whereIn(config(Config::$permissionsTableName).'.id', \array_column($permissions, 'id'));
            });
            
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions, $permissions, $permissionScope) {
                    $query->where(function ($query) use ($rolesWithPermissions, $permissions, $permissionScope) {
                        $query->whereIn(config(Config::$rolesTableName).'.id', \array_column($rolesWithPermissions, 'id'))
                        ->whereHas('permissions', function ($query) use ($permissions, $permissionScope) {
                            $query->where(config(Config::$modelHasPermissionTableName).'.to_id', $permissionScope->to_id)
                                ->where(config(Config::$modelHasPermissionTableName).'.to_type', $permissionScope->to_type);
                        });
                    });
                });
            }
        });

        return $query;
    }

    /**
     * @param string|array|\Ghustavh97\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
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

    // get one permission only
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
     * @param string|int|\Ghustavh97\Larakey\Contracts\Permission $permission
     * @param string|null $guardName
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
     * @param string|int|\Ghustavh97\Larakey\Contracts\Permission $permission
     * @param string|null $guardName
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
    public function hasAnyPermission($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = (array) $permissions;
        }

        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAllPermissions($permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if (! $this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

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
     * @param \Ghustavh97\Larakey\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(...$arguments): bool
    {
        extract($this->getPermissionArguments(__FUNCTION__, $arguments)
                ->only(['permissions', 'guard'])
                ->all());

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
     * @param string|int|\Ghustavh97\Larakey\Contracts\Permission $permission
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission(...$arguments): bool
    {
        extract($this->getPermissionArguments(__FUNCTION__, $arguments)
                ->only(['permissions', 'to', 'recursive', 'guard'])
                ->all());

        $permission = $this->getPermission($permissions, $guard);

        $key = $this->locksmith()->getKey($to);

        return $key->unlocks($this, $permission);
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
     * Grant the given permission(s) to a role.
     *
     * @param mixed| $arguments
     *
     * @return $this
     */
    public function givePermissionTo(...$arguments)
    {
        extract($this->getPermissionArguments(__FUNCTION__, $arguments)
        ->only(['permissions', 'to'])
        ->all());

        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) use ($to) {
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

        if (! $to && config(Config::$strictPermissionAssignment)) {
            throw StrictPermission::assignment();
        }

        $permissions = collect($permissions)->map(function ($permission, $key) use ($to) {
            $permissionScope = $this->locksmith()->getKey($to);

            if ($this->permissions()
                    ->where('id', $permission)
                    ->wherePivot('to_id', $permissionScope->to_id)
                    ->wherePivot('to_type', $permissionScope->to_type)
                    ->first()) {
                return false;
            }

            return array($permission => $permissionScope->getPivot());
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

        if (\method_exists($this, 'forgetCachedRoles') && $this instanceof Role) {
            $this->forgetCachedRoles();
        }

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|\Ghustavh97\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions($permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param \Ghustavh97\Larakey\Contracts\Permission
     * |\Ghustavh97\Larakey\Contracts\Permission[]
     * |string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo(...$arguments)
    {
        extract($this->getPermissionArguments(__FUNCTION__, $arguments)
                ->only(['permissions', 'to', 'recursive'])
                ->all());

        $permissionScope = $this->locksmith()->getKey($to);

        collect($permissions)->each(function ($permission) use ($recursive, $permissionScope) {

            if (! $permission instanceof Permission) {
                $permission = $this->getPermission($permission);
            }

            $detach = $permission->id;

            if ($permissionScope->hasAllAccess()) {
                $permission = $this->permissions()->where('id', $permission->id);

                if (! $recursive) {
                    $permission = $permission->wherePivot('to_id', Larakey::WILDCARD_TOKEN)
                                             ->wherePivot('to_type', Larakey::WILDCARD_TOKEN);
                }
            } elseif ($permissionScope->hasClassAccess()) {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_type', $permissionScope->to_type);

                if (! $recursive) {
                    $permission = $permission->wherePivot('to_id', Larakey::WILDCARD_TOKEN);
                }
            } else {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_id', $permissionScope->to_id)
                                ->wherePivot('to_type', $permissionScope->to_type);
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
     * @param string|array|\Ghustavh97\Larakey\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Ghustavh97\Larakey\Contracts\Permission|\Ghustavh97\Larakey\Contracts\Permission[]|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permission)
    {
        $permissionClass = $this->getPermissionClass();

        if ($permission instanceof Permission) {
            return $permission;
        }

        if (is_numeric($permission)) {
            return $permissionClass->findById(
                $permission,
                $this->getDefaultGuardName()
            );
        }

        if (is_string($permission)) {
            return $permissionClass->findByName(
                $permission,
                $this->getDefaultGuardName()
            );
        }

        if (is_array($permission)) {
            return $permissionClass
            ->whereIn('name', $permission)
            ->whereIn('guard_name', $this->getGuardNames())
            ->get();
        }
    }

    /**
     * @param \Ghustavh97\Larakey\Contracts\Permission|\Ghustavh97\Larakey\Contracts\Role $roleOrPermission
     *
     * @throws \Ghustavh97\Larakey\Exceptions\GuardDoesNotMatch
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
