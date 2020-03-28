# <u>Checking For Permission Via Role</u>

 > The function `hasPermissionViaRole()` can be used to check if a user has a role that has a permission.

## Description

```php
hasPermissionViaRole(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```

### Arguments

- ***$permission***
    - Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    - Description : The permission to give to the user.
- ***$model***
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the permission to limit scope.
- ***$modelId***
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`.
    - Note : ***`$model` must be present when this value is used.***
- ***$guard***
    - Type : `string`
    - Description : The guard to be used with `$permission`.

#### Returns

Returns `boolean`.

---

## Examples

> You can read about roles [here](basic-usage/using-roles/using-roles.md)

```php
// Create role
$role = Role::create(['name' => 'writer']);

// Give permission to role
$role->givePermissionTo(['create', 'edit', 'delete'], Article::class);

$user->hasPermissionViaRole('edit', Article::class); // FALSE

// Assign role to user
$user->assignRole('writer');

// Check for permission
$user->hasPermissionViaRole('edit'); // TRUE
$user->hasPermissionViaRole('edit', Article::class); // TRUE
$user->hasPermissionViaRole('delete'); // TRUE
$user->hasPermissionViaRole('delete', Article::class); // TRUE
```

---
