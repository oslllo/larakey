<?php

namespace Ghustavh97\Guardian\Models;

use Ghustavh97\Guardian\Contracts\PermissionPivot as PermissionPivotContract;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class PermissionPivot extends MorphPivot implements PermissionPivotContract
{
    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['table'] = config('guardian.table_names.model_has_permissions');
        parent::__construct($attributes);
    }
}