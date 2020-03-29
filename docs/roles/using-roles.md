# Using Roles

* [Using permissions With Roles](#using-permissions-with-roles)

---

> ❕ The `assignRole`, `hasRole`, `hasAnyRole`, `hasAllRoles`  and `removeRole` functions can accept a
 string, a `\Oslllo\Larakey\Models\Role` object or an `\Illuminate\Support\Collection` object.

---

## Using permissions with roles

> Any function found in the [Using Permissions](permissions/using-permissions.md) can be used on a `Role` instance since it inherits the `HasPermissions` trait. So you can do stuff like:

```php
// Get writer role
$role = Role::findByName('writer');

// GIve permission to role
$role->givePermissionTo('edit articles');

// Check if role can edit articles
$role->hasPermissionTo('edit articles');

// Revoke permission from role
$role->revokePermissionTo('edit articles');
```

The only diffrence is that we are using `$role` instead of `$user`.

---
