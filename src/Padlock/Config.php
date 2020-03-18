<?php

namespace Oslllo\Larakey\Padlock;

class Config
{
    public static $permissionClass = 'larakey.models.permission';

    public static $modelHasPermissionClass = 'larakey.models.permission_pivot';

    public static $roleClass = 'larakey.models.role';

    public static $rolesTableName = 'larakey.table_names.roles';

    public static $permissionsTableName = 'larakey.table_names.permissions';

    public static $modelHasPermissionTableName = 'larakey.table_names.model_has_permissions';

    public static $modelMorphKeyColumnName = 'larakey.column_names.model_morph_key';

    public static $displayPermissionInException = 'larakey.display_permission_in_exception';

    public static $cacheExpirationTime = 'larakey.cache.expiration_time';

    public static $cachePermissionKey = 'larakey.cache.permission_key';

    public static $cacheRoleKey = 'larakey.cache.role_key';

    public static $cacheModelKey = 'larakey.cache.model_key';

    public static $cacheStore = 'larakey.cache.store';

    public static $strictPermissionAssignment = 'larakey.strict.permission.assignment';

    public static $strictPermissionRevoke = 'larakey.strict.permission.revoke';

    public static $authGuards = 'auth.guards';

    public static $recursionOnPermissionRevoke = 'larakey.recursion_on_permission_revoke';
}
