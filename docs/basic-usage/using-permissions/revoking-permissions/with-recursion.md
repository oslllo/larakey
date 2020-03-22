# <u>Revoking Permissions With Recursion</u>

> To Revoke a permission from a user (using recursion), pass in a boolean of `true` in the `revokePermissionTo()` function. This will remove the permission with those with a lower scope that it.

---

## Examples
```php
// Give user permissions
$user->givePermissionTo('edit');
$user->givePermissionTo('edit', Post::class);
$user->givePermissionTo('edit', Post::class, 1);
```
```php
// Revoke permission with recursion
$user->revokePermissionTo('edit', '*', true);
```
```php
// Check permissions
$user->hasPermissionTo('edit'); // FALSE
$user->hasPermissionTo('edit', '*'); // FALSE
$user->hasPermissionTo('edit', Post::class); // FALSE
$user->hasPermissionTo('edit', Post::class, 1); // FALSE
```

---
