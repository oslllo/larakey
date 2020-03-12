<?php

namespace Ghustavh97\Larakey\Models;

use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Config;
use Ghustavh97\Larakey\Contracts\HasPermission as HasPermissionContract;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Ghustavh97\Larakey\Exceptions\StrictPermission;

class HasPermission extends MorphPivot implements HasPermissionContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType;

    /**
     * The primary key associated with the table.
     *
     * @var string|null
     */
    protected $primaryKey;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * HasPermission constructor.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('larakey.table_names.model_has_permissions'));

        parent::__construct($attributes);
    }

    /**
     * Boot function.
     *
     * @return void
     *
     * @throws \Ghustavh97\Larakey\Exceptions\StrictPermission
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($permission) {
            if (! $permission->to_id || ! $permission->to_type) {
                if (config(Config::$strictPermissionAssignment)) {
                    throw StrictPermission::assignment();
                }

                if (! $permission->to_id) {
                    $permission->to_id = Larakey::WILDCARD_TOKEN;
                }

                if (! $permission->to_type) {
                    $permission->to_type = Larakey::WILDCARD_TOKEN;
                }
            }
        });
    }
}
