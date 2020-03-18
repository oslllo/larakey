# Using permissions via roles

> Any function found in the [Using Permissions](#using-permissions) can be used on a `Role` instance since it inherits the `HasPermissions` trait. So you can do stuff like:

```php

$role = Role::findByName('writer');

$role->givePermissionTo('edit articles');

$role->hasPermissionTo('edit articles');

$role->revokePermissionTo('edit articles');
```

The only diffrence is that we are using `$role` instead of `$user`.

---
