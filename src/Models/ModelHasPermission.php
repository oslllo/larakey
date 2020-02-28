<?php

namespace Ghustavh97\Larakey\Models;

use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Contracts\ModelHasPermission as ModelHasPermissionContract;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Ghustavh97\Larakey\Exceptions\StrictPermission;

class ModelHasPermission extends MorphPivot implements ModelHasPermissionContract
{
    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('larakey.table_names.model_has_permissions'));

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($permission) {
            if (! $permission->to_id || ! $permission->to_type) {
                if (config(Larakey::$strictPermissionAssignment)) {
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
