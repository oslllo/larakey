<?php

namespace Ghustavh97\Guardian\Traits;

use Ghustavh97\Guardian\Guard;
use Illuminate\Support\Collection;
use Ghustavh97\Guardian\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Ghustavh97\Guardian\GuardianRegistrar;
use Ghustavh97\Guardian\Contracts\Permission;
use Ghustavh97\Guardian\Models\ModelHasPermission;
use Ghustavh97\Guardian\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ghustavh97\Guardian\Exceptions\StrictModeRestriction;
use Ghustavh97\Guardian\Exceptions\PermissionDoesNotExist;
use Ghustavh97\Guardian\Exceptions\PermissionNotAssigned;
use Ghustavh97\Guardian\Exceptions\ClassDoesNotExist;

trait GuardianPermissions
{
    use GuardianHelpers;

    private $permissionClass;

    public static function bootGuardianPermissions()
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
            $this->permissionClass = app(GuardianRegistrar::class)->getPermissionClass();
        }

        return $this->permissionClass;
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('guardian.models.permission'),
            'model',
            config('guardian.models.permission_pivot'),
            config('guardian.column_names.model_morph_key'),
            'permission_id'
        )->withPivot(['to_type', 'to_id'])->withTimestamps();

        // return $this->morphToMany(
        //     config('guardian.models.permission'),
        //     'model',
        //     config('guardian.table_names.model_has_permissions'),
        //     config('guardian.column_names.model_morph_key'),
        //     'permission_id'
        // )->using(config('guardian.models.permission_pivot'))->withPivot(['to_type', 'to_id'])->withTimestamps();
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission(Builder $query, $permissions, $pivot = null): Builder
    {
        $pivot = $this->getPivot($pivot);

        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        $query = $query->where(function ($query) use ($permissions, $rolesWithPermissions, $pivot) {
            $query->whereHas('permissions', function ($query) use ($permissions, $pivot) {
                $query->where(config('guardian.table_names.model_has_permissions').'.to_id', $pivot['to_id']);
                $query->where(config('guardian.table_names.model_has_permissions').'.to_type', $pivot['to_type']);

                $query->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere(config('guardian.table_names.permissions').'.id', $permission->id);
                    }
                });
            });
            
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions, $permissions, $pivot) {
                    $query->where(function ($query) use ($rolesWithPermissions, $permissions, $pivot) {
                        foreach ($rolesWithPermissions as $role) {
                            $query->orWhere(config('guardian.table_names.roles').'.id', $role->id)
                            ->whereHas('permissions', function ($query) use ($permissions, $pivot) {
                                $query->where(config('guardian.table_names.model_has_permissions')
                                .'.to_id', $pivot['to_id']);
                                $query->where(config('guardian.table_names.model_has_permissions')
                                .'.to_type', $pivot['to_type']);


                                $query->where(function ($query) use ($permissions) {
                                    foreach ($permissions as $permission) {
                                        $query->orWhere(config('guardian.table_names.permissions')
                                        .'.id', $permission->id);
                                    }
                                });
                            });
                        }
                    });
                });
            }
        });

        return $query;
    }

    /**
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
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

    private function getPermissionModel($to)
    {
        if (is_string($to)) {
            if (! class_exists($to)) {
                throw ClassDoesNotExist::check($to);
            }
            return new $to;
        }

        if ($to instanceof Model) {
            return $to;
        }

        return null;
    }

    // get one permission only
    private function getPermission($permission, $guard = null): Permission
    {
        $permissionClass = $this->getPermissionClass();
        $guard = $guard ? $guard : $this->getDefaultGuardName();

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
     * @param string|int|\Ghustavh97\Guardian\Contracts\Permission $permission
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
     * @param string|int|\Ghustavh97\Guardian\Contracts\Permission $permission
     * @param string|null $guardName
     *
     * @return bool
     */
    public function checkPermissionTo($permission, $attributes = [], $guardName = null): bool
    {
        try {
            return $this->hasPermissionTo($permission, $attributes, $guardName);
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
     * @param \Ghustavh97\Guardian\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(...$arguments): bool
    {
        $arguments = collect($this->getArguments($arguments));
        $guard = $this->getGuard($arguments->get('guard'));
        $permission = $this->getPermission($arguments->get('permissions'), $guard);

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
     * @param string|int|\Ghustavh97\Guardian\Contracts\Permission $permission
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission(...$arguments): bool
    {
        // var_dump('Checking direct permission');
        $arguments = collect($this->getArguments($arguments));

        $guard = $this->getGuard($arguments->get('guard'));
        $permission = $this->getPermission($arguments->get('permissions'), $guard);
        $model = $arguments->get('model');
        $pivot = $this->getPivot($model);

        return $this->permissions->contains(function ($thisPermission, $key) use ($model, $permission, $pivot) {
            return (string) $thisPermission->id === (string) $permission->id
                && ((string) $thisPermission->to_id === (string) $pivot['to_id'] ||
                    (string) $thisPermission->to_id === '*')
                && ((string) $thisPermission->to_type === (string) $pivot['to_type'] ||
                    (string) $thisPermission->to_type === '*');
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
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$arguments)
    {
        $arguments = collect($this->getArguments($arguments));
        $permissions = $arguments->get('permissions');
        $model = $arguments->get('model');

        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) use ($model) {
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

        $model = (object) [
            'this' => $this->getModel(),
            'to' => $model
        ];

        if (! $model->to && config('guardian.strict.permission.assignment')) {
            throw StrictModeRestriction::assignment();
        }

        $permissions = collect($permissions)->map(function ($permission, $key) use ($model) {
            $pivot = $this->getPivot($model->to);

            if ($this->permissions()
                    ->where('id', $permission)
                    ->wherePivot('to_id', $pivot['to_id'])
                    ->wherePivot('to_type', $pivot['to_type'])
                    ->first()) {
                return false;
            }

            return array($permission => $pivot);
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
        
        if ($model->this->exists) {
            $this->permissions()->attach($permissions);
            $model->this->load('permissions');
        } else {
            $class = \get_class($model->this);

            $class::saved(
                function ($object) use ($permissions, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model->this) {
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
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
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
     * @param \Ghustavh97\Guardian\Contracts\Permission
     * |\Ghustavh97\Guardian\Contracts\Permission[]
     * |string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo(...$arguments)
    {
        $arguments = collect($this->getArguments($arguments));
        $permissions = $arguments->get('permissions');
        $model = $arguments->get('model');
        $recursive = $arguments->get('recursive');
        $pivot = $this->getPivot($model);

        foreach ($permissions as $permission) {
            if (! $permission instanceof Permission) {
                $permission = $this->getPermission($permission);
            }

            $id = $permission->id;

            if ($pivot['to_type'] === '*' && $pivot['to_id'] === '*') {
                $permission = $this->permissions()->where('id', $permission->id);
                if (! $recursive) {
                    $permission = $permission->wherePivot('to_type', '*')
                                             ->wherePivot('to_type', '*');
                }
            } elseif (($pivot['to_type'] !== '*' && $pivot['to_id'] === '*')) {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_type', $pivot['to_type']);
                if (! $recursive) {
                    $permission = $permission->wherePivot('to_id', '*');
                }
            } else {
                $permission = $this->permissions()
                                ->where('id', $permission->id)
                                ->wherePivot('to_type', $pivot['to_type'])
                                ->wherePivot('to_id', $pivot['to_id']);
            }

            if (! $permission->first()) {
                throw new PermissionNotAssigned;
            }

            $permission->detach($id);
        }

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
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Ghustavh97\Guardian\Contracts\Permission|\Ghustavh97\Guardian\Contracts\Permission[]|\Illuminate\Support\Collection
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
                $this->getDefaultGuardName(),
            );
        }

        if (is_string($permission)) {
            return $permissionClass->findByName(
                $permission,
                $this->getDefaultGuardName(),
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
     * @param \Ghustavh97\Guardian\Contracts\Permission|\Ghustavh97\Guardian\Contracts\Role $roleOrPermission
     *
     * @throws \Ghustavh97\Guardian\Exceptions\GuardDoesNotMatch
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
        app(GuardianRegistrar::class)->forgetCachedPermissions($reload);
    }
}
