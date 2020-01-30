<?php

namespace Ghustavh97\Guardian\Models;

use Ghustavh97\Guardian\Contracts\PermissionPivot as PermissionPivotContract;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class PermissionPivot extends MorphPivot implements PermissionPivotContract
{
    // protected $table;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    // protected $appends = ['to_type', 'to_id'];

    public function __construct(array $attributes = [])
    {
        $attributes['table'] = config('guardian.table_names.model_has_permissions');

        // $this->appends = array_merge($this->appends, ['to_type', 'to_id']);
        // $attributes['incrementing'] = false;
        parent::__construct($attributes);
        // $this->table = config('permission.table_names.model_has_permissions');
        // $this->incrementing = true;
    }

    // public function getToIdAttribute()
    // {
    //     return $this->to_id;
    // }

    // public function getToTypeAttribute()
    // {
    //     return $this->to_type;
    // }

}