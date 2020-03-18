## Checking for direct permissions

```php
// Check if the user has All direct permissions
$user->hasAllDirectPermissions(['edit articles', 'delete articles']);

// Check if the user has Any permission directly
$user->hasAnyDirectPermission(['create articles', 'delete articles']);
```
By following the previous example, when we call `$user->hasAllDirectPermissions(['edit articles', 'delete articles'])` 
it returns `true`, because the user has all these direct permissions. 
When we call
`$user->hasAnyDirectPermission('edit articles')`, it returns `true` because the user has one of the provided permissions.


You can list all of these permissions:

```php
// Direct permissions
$user->getDirectPermissions() // Or $user->permissions;

// Permissions inherited from the user's roles
$user->getPermissionsViaRoles();

// All permissions which apply on the user (inherited and direct)
$user->getAllPermissions();
```

All these responses are collections of `Oslllo\Larakey\Models\Permission` objects.



If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.

If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.


---