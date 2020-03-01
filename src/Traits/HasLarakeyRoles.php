<?php

namespace Ghustavh97\Larakey\Traits;

use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Cache;
use Ghustavh97\Larakey\Padlock\Config;
use Illuminate\Support\Collection;
use Ghustavh97\Larakey\Contracts\Role;
use Illuminate\Database\Eloquent\Builder;
use Ghustavh97\Larakey\Exceptions\RoleDoesNotExist;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasLarakeyRoles
{
    use HasLarakeyPermissions;

    private $roleClass;

    public static function bootHasLarakeyRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
        });
    }

    public function getRoleClass()
    {
        if (! isset($this->roleClass)) {
            $this->roleClass = app(Larakey::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('larakey.models.role'),
            'model',
            config('larakey.table_names.model_has_roles'),
            config('larakey.column_names.model_morph_key'),
            'role_id'
        )->withTimestamps();
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Ghustavh97\Larakey\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string $guard
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (! is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';
            $guard = $guard ?: $this->getDefaultGuardName();

            return $this->getRoleClass()->{$method}($role, $guard);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn(config(Config::$rolesTableName).'.id', \array_column($roles, 'id'));
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|\Ghustavh97\Larakey\Contracts\Role ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->roles()->sync($roles, false);
            $model->load('roles');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                        return;
                    }
                    $object->roles()->sync($roles, false);
                    $object->load('roles');
                    $modelLastFiredOn = $object;
                }
            );
        }

        //! I don't see the use of this

        $this->forgetCachedPermissions();

        // $this->forgetCachedRoles(true);
        $this->forgetCachedRoles();


        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|\Ghustavh97\Larakey\Contracts\Role $role
     */
    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));

        $this->load('roles');

        $this->forgetCachedPermissions();

        $this->forgetCachedRoles();

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param  array|\Ghustavh97\Larakey\Contracts\Role|string  ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    public function getRole($role, $guard = null): Role
    {
        $roleClass = $this->getRoleClass();
        $guard = $guard ? $guard : $this->getDefaultGuardName();

        if (is_array($role)) {
            $role = $role[0];
        }

        if (is_string($role)) {
            $role = $roleClass->findByName($role, $guard);
        }

        if (is_int($role)) {
            $role = $roleClass->findById($role, $guard);
        }

        if (! $role instanceof Role) {
            throw new RoleDoesNotExist;
        }

        return $role;
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|int|array|\Ghustavh97\Larakey\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string|null $guard
     * @return bool
     */

    public function hasRole($roles, string $guard = null, bool $returnRole = false)
    {
        $roleClass = $this->getRoleClass();
        $guard = $guard ? $guard : $this->getDefaultGuardName();

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->locksmith()->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            $query = $this->roles->where('name', $roles);
        }

        if (is_int($roles)) {
            $query = $this->roles->where('id', $roles);
        }

        if ($roles instanceof Role) {
            $query = $this->roles->where('id', $roles->id);
        }

        if (isset($query)) {
            $query = $query->where('guard_name', $guard);
        }

        $role = isset($query) ? $query->first() : null;

        if (! $role && is_array($roles)) {
            collect($roles)->each(function ($value) use (&$role, $guard) {
                if ($role = $this->hasRole($value, $guard, true)) {
                    return false;
                }
            });
        }

        if (! $role && $roles instanceof Collection) {
            $role = $roles->intersect($guard ? $this->roles->where('guard_name', $guard) : $this->roles)->first();
        }

        return $returnRole ? $role : boolval($role);
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * Alias to hasRole() but without Guard controls
     *
     * @param string|int|array|\Ghustavh97\Larakey\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param  string|array|\Ghustavh97\Larakey\Contracts\Role|\Illuminate\Support\Collection  $roles
     * @param  string|null  $guard
     * @return bool
     */
    public function hasAllRoles($roles, string $guard = null): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->locksmith()->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $guard
                ? $this->roles->where('guard_name', $guard)->contains('name', $roles)
                : $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect(
            $guard
                ? $this->roles->where('guard_name', $guard)->pluck('name')
                : $this->getRoleNames()
        ) == $roles;
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    protected function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    /**
     * Forget the cached roles.
     */
    public function forgetCachedRoles($reload = false)
    {
        app(Cache::class)->forgetCachedRoles($reload);
    }
}
