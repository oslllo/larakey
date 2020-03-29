# Checking For Permissions

* [Checking For Direct Permissions](#checking-for-direct-permissions)
* [Checking For Any Permissions](#checking-for-any-permissions)
* [Checking For All Permissions](#checking-for-all-permissions)
* [Checking Permissions Via Roles](#checking-for-permissions-via-roles)

> The function `hasPermissionTo()` OR `can()` can be used to check if a user has direct permission or permission via role.

## Description

```php
hasPermissionTo(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```

### Arguments

* ***$permission***
    * Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    * Description : The permission to give to the user.
* ***$model***
    * Type : `string` | `\Illuminate\Database\Eloquent\Model`
    * Description : The model class or instance to be used with the permission to limit scope.
* ***$modelId***
    * Type : `string` | `int`
    * Description : Used to indicate the id of a model when only a class name string is provided to `$model`.
    * Note : ***`$model` must be present when this value is used.***
* ***$guard***
    * Type : `string`
    * Description : The guard to be used with `$permission`.

#### Returns

Returns `boolean`.

---

## Examples

```php
//Check for permissions
$post = Post::find(1);

$user->hasPermissionTo('edit');
$user->hasPermissionTo('edit', '*');
$user->hasPermissionTo('edit', $post);
$user->hasPermissionTo('edit', Post::class);
$user->hasPermissionTo('edit', Post::class, $post->id);
$user->hasPermissionTo(['edit', 'delete'], Post::class, $post->id);
```

Also see [Assigning Permissions Examples](permissions/assigning-permissions.md) OR [Revoking Permissions Examples](permissions/revoking-permissions.md)

You may pass an integer representing the permission id.

```php
//Check for permissions
$user->hasPermissionTo('1');
$user->hasPermissionTo(Permission::find(1)->id);
$user->hasPermissionTo($somePermission->id);
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate` class for the default guard. So you can
check if a user has a permission with Laravel's default `can` function:

```php
//Check for permissions
$user->can('edit', Article::class);
```

---

## Checking For Direct Permissions

 > The function `hasDirectPermission()` can be used to check if a user has a direct permission.

## Description

```php
hasDirectPermission(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```

### Arguments

* ***$permission***
    * Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    * Description : The permission to give to the user.
* ***$model***
    * Type : `string` | `\Illuminate\Database\Eloquent\Model`
    * Description : The model class or instance to be used with the permission to limit scope.
* ***$modelId***
    * Type : `string` | `int`
    * Description : Used to indicate the id of a model when only a class name string is provided to `$model`.
    * Note : ***`$model` must be present when this value is used.***
* ***$guard***
    * Type : `string`
    * Description : The guard to be used with `$permission`.

#### Returns

Returns `boolean`.

## Examples

```php
$post = Post::find(1);

$user->hasDirectPermission('edit');
$user->hasDirectPermission('edit', '*');
$user->hasDirectPermission('edit', $post);
$user->hasDirectPermission('edit', Post::class);
$user->hasDirectPermission('edit', Post::class, $post->id);
$user->hasDirectPermission(['edit', 'delete'], Post::class, $post->id);
```

---

## Checking For Any Permissions

 > The function `hasAnyPermission()` can be used to check if a user has any of the permissions.

## Description

```php
hasAnyPermission(array $permissions): bool
```

### Arguments

* ***$permissions***
    * Type :  `array`
    * Description : The array of permissions to check.

#### Returns

Returns `boolean`.

## Examples

```php
$post = Post::find(1);

$user->hasAnyPermission([
    'view',
    ['view', '*'],
    ['create', Post::class],
    ['edit', Post::class, 1],
    ['delete', $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], Post::class, 1],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost, 'web']
]);

```

---

## Checking For All Permissions

 > The function `hasAllPermissions()` can be used to check if a user has all of the given permissions.

## Description

```php
hasAllPermissions(array $permissions): bool
```

### Arguments

* ***$permissions***
    * Type :  `array`
    * Description : The array of permissions to check.

#### Returns

Returns `boolean`.

## Examples

```php
$post = Post::find(1);

$user->hasAllPermissions([
    'view',
    ['view', '*'],
    ['create', Post::class],
    ['edit', Post::class, 1],
    ['delete', $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], Post::class, 1],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost],
    [['view', 'create', 'edit', 'delete'], $this->testUserPost, 'web']
]);

```

---

## Checking For Permissions Via Roles

 > The function `hasPermissionViaRole()` can be used to check if a user has a role that has a permission.

## Description

```php
hasPermissionViaRole(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```

### Arguments

* ***$permission***
    * Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    * Description : The permission to give to the user.
* ***$model***
    * Type : `string` | `\Illuminate\Database\Eloquent\Model`
    * Description : The model class or instance to be used with the permission to limit scope.
* ***$modelId***
    * Type : `string` | `int`
    * Description : Used to indicate the id of a model when only a class name string is provided to `$model`.
    * Note : ***`$model` must be present when this value is used.***
* ***$guard***
    * Type : `string`
    * Description : The guard to be used with `$permission`.

#### Returns

Returns `boolean`.

---

## Examples

> You can read about roles [here](roles/using-roles.md)

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
