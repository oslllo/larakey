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
use Ghustavh97\Guardian\Exceptions\ClassDoesNotExist;

trait HasPermissions
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

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
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

    private function getArguments(array $arguments): array
    {
        $argumentCount = count($arguments);

        if ($argumentCount > 3) {
            // TODO: Throw too many arguments exception.
        }

        $permissions = $arguments[0];
        $model = null;
        $guardName = null;

        if (is_string($permissions) && false !== strpos($permissions, '|')) {
            $permissions = $this->convertPipeToArray($permissions);
        }

        if (is_string($permissions) || is_object($permissions)) {
            $permissions = [$permissions];
        }

        if ($argumentCount >= 2) {
            $secondArgument = $arguments[1];
        }
        
        if ($argumentCount == 2) {
            if ((\is_string($secondArgument) && \class_exists($secondArgument)) || $secondArgument instanceof Model) {
                $model = $this->getPermissionModel($secondArgument);
            } else {
                $guardName = $secondArgument;
            }
        }

        if ($argumentCount >= 3) {
            $thirdArgument = $arguments[2];
        }

        if ($argumentCount == 3) {
            if ($argumentCount == 3) {
                $model = $this->getPermissionModel($secondArgument);
                $guardName = $thirdArgument;
            }
        }

        $data = [
            'permissions' => $permissions,
            'model' => $model,
            'guard' => $guardName
        ];

        return $data;
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

    private function getPivot($model): Array
    {
        $toId = $model && $model->exists ? $model->id : null;
        $toType = $model ? get_class($model) : '*';
        return ['to_id' => $toId, 'to_type' => $toType];
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

    private function getGuard($guard): String
    {
        return $guard ? $guard : $this->getDefaultGuardName();
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
     * @deprecated since 2.35.0
     * @alias of hasPermissionTo()
     */
    public function hasUncachedPermissionTo($permission, $guardName = null): bool
    {
        return $this->hasPermissionTo($permission, $guardName);
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

        if (! $role = $this->hasRole($permission->roles, $guard, true)) {
            return false;
        }

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
        $arguments = collect($this->getArguments($arguments));

        $guard = $this->getGuard($arguments->get('guard'));
        $permission = $this->getPermission($arguments->get('permissions'), $guard);
        $model = $arguments->get('model');

        $pivot = $this->getPivot($model);

        //TODO: fix id types issue.

        return $this->permissions->contains(function ($modelPermission, $key) use($model, $permission, $pivot) {
            $modelPermissionId = ($model && $model->incrementing) ? (int) $modelPermission->id : $modelPermission->id;
            return $modelPermissionId === $permission->id 
                && (string) $modelPermission->to_id === (string) $pivot['to_id']
                && (string) $modelPermission->to_type === (string) $pivot['to_type'];
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

        if(! $model->to && config('guardian.strict.permission.assignment')) {
            throw StrictModeRestriction::assignment();
        }

        $permissions = collect($permissions)->map(function ($permission, $key) use($model) {
            if (! $model->to) {
                $this->permissions()->detach($permission);
            } elseif ($model->to && ! $model->to->exists) {
                $this->permissions()->detach($permission, ['to_type' => \get_class($model->to)]);
            }
            return [
                $permission => [
                    'to_id' => $model->to && $model->to->exists ? $model->to->id : null,
                    'to_type' => $model->to ? \get_class($model->to) : '*'
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

    private function getPermissionAttributes(array $attributes): array
    {

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
    public function revokePermissionTo($permission, $attributes = [])
    {
        if(is_string($attributes)) {
            $attributes = [$attributes];
        }

        $permission = $this->getStoredPermission($permission, $attributes);

        if (count($attributes)) {
            $detach = $this->permissions->where('to_type', $attributes[0])->where('to_id', null)->where('id', $permission->id);
        } else {
            $detach = $permission;
        }

        $this->permissions()->detach($detach);

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
