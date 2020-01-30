<?php

namespace Ghustavh97\Guardian\Traits;

use Ghustavh97\Guardian\Guard;
use Illuminate\Support\Collection;
use Ghustavh97\Guardian\Contracts\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Ghustavh97\Guardian\GuardianRegistrar;
use Ghustavh97\Guardian\Contracts\Permission;
use Ghustavh97\Guardian\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ghustavh97\Guardian\Exceptions\StrictModeRestriction;
use Ghustavh97\Guardian\Exceptions\PermissionDoesNotExist;

trait HasPermissions2
{
    private $permissionClass;

    public static function bootHasPermissions()
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
            config('guardian.table_names.model_has_permissions'),
            config('guardian.column_names.model_morph_key'),
            'permission_id'
        )->using(config('guardian.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function ($query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                $query->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere(config('guardian.table_names.permissions').'.id', $permission->id);
                    }
                });
            });
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->where(function ($query) use ($rolesWithPermissions) {
                        foreach ($rolesWithPermissions as $role) {
                            $query->orWhere(config('guardian.table_names.roles').'.id', $role->id);
                        }
                    });
                });
            }
        });
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

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|int|\Ghustavh97\Guardian\Contracts\Permission $permission
     * @param string|null $guardName
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo($permission, $attributes = [], $guardName = null): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName(),
            );
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName(),
            );
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        //TODO: Get permission with attribute
    
        return $this->hasDirectPermission($permission, $attributes) || $this->hasPermissionViaRole($permission, $attributes);
    }

    /**
     * @deprecated since 2.35.0
     * @alias of hasPermissionTo()
     */
    public function hasUncachedPermissionTo($permission, $guardName = null): bool
    {
        return $this->hasPermissionTo($permission, null, $guardName);
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
            return $this->hasPermissionTo(func_get_args ());
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
    protected function hasPermissionViaRole(Permission $permission, $attributes = []): bool
    {

        // $this->roles->get()
        // $test = $this->roles->contains(function ($modelRole, $key) use ($permission) {
        //     return $permission->roles->contatins(function ($permissionRole, $key) {
        //         return 
        //     });
        // });

        if(! $role = $this->hasRoleAndReturn($permission->roles)) {
            return false;
        }

        // return true;

        return $role->hasDirectPermission($permission);
        
        // if(count($permission->roles))dd($permission->roles);
        // if (! $this->hasRole($permission->roles)) {
        //     return false;
        // } else {

        //     $role = $this->getPermissionRole($permission);

        //     if(! $role->exists) {
        //         return false;
        //     }
        //     return true;
        //     return $role->permissions->contains(function ($rolePermission, $key) use ($permission) {
        //         // return $rolePermission->id;
        //     });
        // }
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|int|\Ghustavh97\Guardian\Contracts\Permission $permission
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission($permission, $attributes = []): bool
    {
        dd(\func_get_args());
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        $model = app(GuardianRegistrar::class)->getModelFromAttributes($attributes);
        
        $to = [
            'id' => $model && $model->exists ? $model->id : null,
            'type' => $model ? get_class($model) : '*'
        ];

        return $this->permissions->contains(function ($modelPermission, $key) use($model, $permission, $to) {
            return $modelPermission->id === $permission->id 
                && $modelPermission->to_id === $to['id'] 
                && $modelPermission->to_type === $to['type'];
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

        // if(is_string($permissions) || $permissions instanceof Permission) {
        //     $permissions = [$permissions];
        // } elseif (! is_array($permissions) && $permissions != null && ! $permissions instanceof Collection) {
        //     // dd($permissions);
        // }

        return collect($permissions);
    }
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|\Ghustavh97\Guardian\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo($permissions, $attributes = [])
    {
        if (is_string($attributes) || $attributes instanceof Model) {
            $attributes = [$attributes];
        }

        $permissions = $this->permissionsToCollection($permissions)
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

        $model = (object) [
            'this' => $this->getModel(),
            'to' => app(GuardianRegistrar::class)->getModelFromAttributes($attributes)
        ];

        if(! $model->to && config('guardian.strict.permission.assignment')) {
            throw StrictModeRestriction::assignment();
        }

        $permissions = collect($permissions)->map(function ($permission, $key) use($model) {
            if (! $model->to) {
                $this->permissions()->detach($permission);
            } elseif ($model->to && ! $model->to->exists) {
                $this->permissions()->detach($permission, ['to_type' => get_class($model->to)]);
            }
            return [
                $permission => [
                    'to_id' => $model->to && $model->to->exists ? $model->to->id : null,
                    'to_type' => $model->to ? get_class($model->to) : '*'
                ]
            ];
        })->all();
        
        $temp = [];

        foreach($permissions as $permission) {
            foreach(array_keys($permission) as $permissionKey) {
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
     * @param \Ghustavh97\Guardian\Contracts\Permission|\Ghustavh97\Guardian\Contracts\Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

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
    protected function getStoredPermission($permissions)
    {
        $permissionClass = $this->getPermissionClass();

        if (is_numeric($permissions)) {
            return $permissionClass->findById($permissions, $this->getDefaultGuardName());
        }

        if (is_string($permissions)) {
            return $permissionClass->findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return $permissionClass
                ->whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
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

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(GuardianRegistrar::class)->forgetCachedPermissions();
    }
}
