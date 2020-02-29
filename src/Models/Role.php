<?php

namespace Ghustavh97\Larakey\Models;

use Ghustavh97\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Cache;
use Ghustavh97\Larakey\Traits\HasLarakeyPermissions;
use Ghustavh97\Larakey\Exceptions\RoleDoesNotExist;
use Ghustavh97\Larakey\Exceptions\GuardDoesNotMatch;
use Ghustavh97\Larakey\Exceptions\RoleAlreadyExists;
use Ghustavh97\Larakey\Contracts\Role as RoleContract;
use Ghustavh97\Larakey\Traits\RefreshesLarakeyCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model implements RoleContract
{
    use HasLarakeyPermissions;
    use RefreshesLarakeyCache;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('larakey.table_names.roles'));
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        $role = static::getRoles(['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']])->first();

        if ($role) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->morphToMany(
            config('larakey.models.permission'),
            'model',
            config('larakey.table_names.model_has_permissions'),
            config('larakey.column_names.model_morph_key'),
            'permission_id'
        )->using(config('larakey.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('larakey.table_names.model_has_roles'),
            'role_id',
            config('larakey.column_names.model_morph_key')
        );
    }

    /**
     * Find a role by its name and guard name.
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Ghustavh97\Larakey\Contracts\Role|\Ghustavh97\Larakey\Models\Role
     *
     * @throws \Ghustavh97\Larakey\Exceptions\RoleDoesNotExist
     */
    public static function findByName(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::findRoleByQuery(['name' => $name, 'guard_name' => $guardName]);

        if (! $role) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    public static function findById(int $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::findRoleByQuery(['id' => $id, 'guard_name' => $guardName]);

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    private static function findRoleByQuery(array $query)
    {
        $role = static::getRoles($query);
        
        return $role->first();
    }

    /**
     * Find or create role by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Ghustavh97\Larakey\Contracts\Role
     */
    public static function findOrCreate(string $name, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::getRoles(['name' => $name, 'guard_name' => $guardName])->first();

        if (! $role) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }

    /**
     * Get the current cached roles.
     */
    protected static function getRoles(array $params = []): Collection
    {
        app(Larakey::class)->setRoleClass(static::class);
        
        return app(Cache::class)->getRoles($params);
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     *
     * @throws \Ghustavh97\Larakey\Exceptions\GuardDoesNotMatch
     */
    public function hasPermissionTo($permission): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (! $this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
        }
        
        return $this->hasDirectPermission($permission);
    }
}
