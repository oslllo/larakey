<?php

namespace Ghustavh97\Guardian\Models;

use Ghustavh97\Guardian\Contracts\ModelHasPermission as ModelHasPermissionContract;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ModelHasPermission extends MorphPivot implements ModelHasPermissionContract
{
    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    protected $table;

    public function __construct(array $attributes = [])
    {
        $this->table = config('guardian.table_names.model_has_permissions');
        // $this->setModelIdAttribute = function ($value) {
        //     $this->attributes['model_id'] = (int) $value;
        // };
        parent::__construct($attributes);
    }
}
