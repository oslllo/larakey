<?php

namespace Oslllo\Larakey\Models;

use Oslllo\Larakey\Guard;
use Illuminate\Support\Collection;
use Oslllo\Larakey\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Traits\RefreshesLarakeyCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Oslllo\Larakey\Exceptions\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oslllo\Larakey\Exceptions\PermissionAlreadyExists;
use Oslllo\Larakey\Larakey;
use Oslllo\Larakey\Padlock\Config;
use Oslllo\Larakey\Padlock\Key;
use Oslllo\Larakey\Padlock\Cache;
use Oslllo\Larakey\Traits\LarakeyHelpers;
use Oslllo\Larakey\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use HasRoles;
    use LarakeyHelpers;
    use RefreshesLarakeyCache;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Permission constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config(Config::$permissionsTableName));

        $this->appends = array_merge($this->appends, ['to_type', 'to_id']);
    }

    /**
     * Create permission.
     *
     * @param array $attributes
     *
     * @return \Oslllo\Larakey\Models\Permission|\Oslllo\Larakey\Contracts\Permission
     *
     * @throws \Oslllo\Larakey\Exceptions\PermissionAlreadyExists
     */
    public static function create(array $attributes = []): PermissionContract
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        $permission = static::getPermissions(
            ['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']]
        )->first();

        if ($permission) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->morphedByMany(
            config('larakey.models.role'),
            'model',
            config('larakey.table_names.model_has_permissions'),
            'permission_id',
            config('larakey.column_names.model_morph_key')
        )->using(config('larakey.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('larakey.table_names.model_has_permissions'),
            'permission_id',
            config('larakey.column_names.model_morph_key')
        )->using(config('larakey.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Oslllo\Larakey\Contracts\Permission
     *
     * @throws \Oslllo\Larakey\Exceptions\PermissionDoesNotExist
     */
    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::findPermissionByQuery(['name' => $name, 'guard_name' => $guardName]);
        
        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    /**
     * Find a permission by its id (and optionally guardName).
     *
     * @param int $id
     * @param string|null $guardName
     *
     * @return \Oslllo\Larakey\Contracts\Permission
     *
     * @throws \Oslllo\Larakey\Exceptions\PermissionDoesNotExist
     */
    public static function findById(int $id, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::findPermissionByQuery(['id' => $id, 'guard_name' => $guardName]);

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    /**
     * Find permission by query.
     *
     * @param array $query
     *
     * @return null|\Oslllo\Larakey\Models\Permission|\Oslllo\Larakey\Contracts\Permission
     */
    private static function findPermissionByQuery(array $query)
    {
        $permission = static::getPermissions($query);

        return $permission->first();
    }

    /**
     * Find or create permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Oslllo\Larakey\Contracts\Permission
     */
    public static function findOrCreate(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();

        if (! $permission) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     *
     * @param array $params
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function getPermissions(array $params = []): Collection
    {
        app(Larakey::class)->setPermissionClass(static::class);
        
        return app(Cache::class)->getCachedPermissions($params);
    }

    /**
     * Get permission to_id attribute.
     *
     * @return void|string
     */
    public function getToIdAttribute()
    {
        if ($this->pivot) {
            return $this->pivot->to_id;
        }
    }

    /**
     * Get permission to_type attribute.
     *
     * @return void|string
     */
    public function getToTypeAttribute()
    {
        if ($this->pivot) {
            return $this->pivot->to_type;
        }
    }
}
