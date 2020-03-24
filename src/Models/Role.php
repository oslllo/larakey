<?php

namespace Oslllo\Larakey\Models;

use Oslllo\Larakey\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Larakey;
use Oslllo\Larakey\Padlock\Cache;
use Oslllo\Larakey\Traits\HasPermissions;
use Oslllo\Larakey\Traits\HasRoles;
use Oslllo\Larakey\Exceptions\RoleDoesNotExist;
use Oslllo\Larakey\Exceptions\RoleAlreadyExists;
use Oslllo\Larakey\Contracts\Role as RoleContract;
use Oslllo\Larakey\Traits\RefreshesLarakeyCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oslllo\Larakey\Traits\LarakeyHelpers;

class Role extends Model implements RoleContract
{
    use HasRoles;
    use HasPermissions;
    use LarakeyHelpers;
    use RefreshesLarakeyCache;

    /**
     * The attributes that are not mass assignable
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Role constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('larakey.table_names.roles'));
    }

    /**
     * Create role
     *
     * @param array $attributes
     *
     * @return \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
     *
     * @throws \Oslllo\Larakey\Exceptions\RoleAlreadyExists
     */
    public static function create(array $attributes = []): RoleContract
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
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
     * Find a role by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
     *
     * @throws \Oslllo\Larakey\Exceptions\RoleDoesNotExist
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

    /**
     * Find a role by its id (and optionally guardName).
     *
     * @param integer $id
     * @param string|null $guardName
     *
     * @return \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
     *
     * @throws \Oslllo\Larakey\Exceptions\RoleDoesNotExist
     */
    public static function findById(int $id, $guardName = null): RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::findRoleByQuery(['id' => $id, 'guard_name' => $guardName]);

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Find a role by query.
     *
     * @param array $query
     *
     * @return null|\Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
     */
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
     * @return \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
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
     * Get a collection of roles.
     *
     * @param array $params
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function getRoles(array $params = []): Collection
    {
        app(Larakey::class)->setRoleClass(static::class);

        return app(Cache::class)->getCachedRoles($params);
    }
}
