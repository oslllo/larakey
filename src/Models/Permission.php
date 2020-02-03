<?php

namespace Ghustavh97\Guardian\Models;

use Ghustavh97\Guardian\Guard;
use Illuminate\Support\Collection;
use Ghustavh97\Guardian\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Guardian\GuardianRegistrar;
use Ghustavh97\Guardian\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ghustavh97\Guardian\Exceptions\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ghustavh97\Guardian\Exceptions\PermissionAlreadyExists;
use Ghustavh97\Guardian\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use HasRoles;
    use RefreshesPermissionCache;

    protected $guarded = ['id'];

    protected $appends = [];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('guardian.table_names.permissions'));

        $this->appends = array_merge($this->appends, ['to_type', 'to_id']);

        $model = $this->getModel();
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        $permission = static::getPermissions(['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']])->first();

        if ($permission) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    public function setIdAttribute($value) {
        $this->attributes['id'] = (int) $value;
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->morphedByMany(
            config('guardian.models.role'),
            'model',
            config('guardian.table_names.model_has_permissions'),
            'permission_id',
            config('guardian.column_names.model_morph_key')
        )->using(config('guardian.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('guardian.table_names.model_has_permissions'),
            'permission_id',
            config('guardian.column_names.model_morph_key')
        )->using(config('guardian.models.permission_pivot'))->withPivot(['to_type', 'to_id']);
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @throws \Ghustavh97\Guardian\Exceptions\PermissionDoesNotExist
     *
     * @return \Ghustavh97\Guardian\Contracts\Permission
     */
    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::permissionQueryBuilder(['name' => $name, 'guard_name' => $guardName]);
        
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
     * @throws \Ghustavh97\Guardian\Exceptions\PermissionDoesNotExist
     *
     * @return \Ghustavh97\Guardian\Contracts\Permission
     */
    public static function findById(int $id, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        $permission = static::permissionQueryBuilder(['id' => $id, 'guard_name' => $guardName]);

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    private static function permissionQueryBuilder(array $query)
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
     * @return \Ghustavh97\Guardian\Contracts\Permission
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
     */
    protected static function getPermissions(array $params = []): Collection
    {
        return app(GuardianRegistrar::class)
            ->setPermissionClass(static::class)
            ->getPermissions($params);
    }

    public function getToIdAttribute()
    {
        if($this->pivot) {
            return $this->pivot->to_id;
        }
    }

    public function getToTypeAttribute()
    {
        if($this->pivot) {
            return $this->pivot->to_type;
        }
    }
}
