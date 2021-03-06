<?php

return [

    'strict' => [

        'permission' => [

            /*
            * When set to true, assigning a permission will require that you specify the
            * permission's scope or it throws `Oslllo\Larakey\Exceptions\StrictPermission`
            *
            * For example
            *
            * If set to true:
            *
            * $user->givePermissionTo('view', '*') passes.
            * $user->givePermissionTo('view') throws `Oslllo\Larakey\Exceptions\StrictPermission`
            *
            * If set to false:
            *
            * $user->givePermissionTo('view', '*') passes.
            * $user->givePermissionTo('view') passes.
            */

            'assignment' => false,

            /*
            * When set to true, revoking a permission will require that you specify the
            * permission's scope or it throws `Oslllo\Larakey\Exceptions\StrictPermission`
            *
            * For example
            *
            * If set to true:
            *
            * $user->revokePermission('view', '*') passes.
            * $user->revokePermission('view') throws `Oslllo\Larakey\Exceptions\StrictPermission`
            *
            * If set to false:
            *
            * $user->revokePermission('view', '*') passes.
            * $user->revokePermission('view') passes.
            */

            'revoke' => false

        ]

    ],

    /*
    * Revoke permission and others of lower scope recursively.
    *
    * `*` > Class > Instance
    */

    'recursion_on_permission_revoke' => false,

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Oslllo\Larakey\Contracts\Permission` contract.
         */

        'permission' => Oslllo\Larakey\Models\Permission::class,

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Oslllo\Larakey\Contracts\HasPermission` contract.
         */

        'permission_pivot' => Oslllo\Larakey\Models\HasPermission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Oslllo\Larakey\Contracts\Role` contract.
         */

        'role' => Oslllo\Larakey\Models\Role::class,

    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'larakey_roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'larakey_permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'larakey_model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'larakey_model_has_roles',
    ],

    'column_names' => [

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',
    ],

    /*
     * When set to true, the required permission/role names are added to the exception
     * message. This could be considered an information leak in some contexts, so
     * the default setting is false here for optimum safety.
     */

    'display_permission_in_exception' => false,

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */

        'permission_key' => 'larakey.permission.cache',

        /*
         * The cache key used to store all roles.
         */

        'role_key' => 'larakey.role.cache',

        /*
         * When checking for a permission against a model by passing a Permission
         * instance to the check, this key determines what attribute on the
         * Permissions model is used to cache against.
         *
         * Ideally, this should match your preferred way of checking permissions, eg:
         * `$user->can('view-posts')` would be 'name'.
         */

        'model_key' => 'name',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],
];
