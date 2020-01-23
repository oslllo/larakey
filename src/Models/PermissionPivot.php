<?php

namespace Ghustavh97\Guardian\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class PermissionPivot extends MorphPivot
{
    // protected $table;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $attributes['table'] = config('permission.table_names.model_has_permissions');
        // $attributes['incrementing'] = false;
        parent::__construct($attributes);
        // $this->table = config('permission.table_names.model_has_permissions');
        // $this->incrementing = true;
    }
}