# Checking For Permission Via Role
 > The function `hasPermissionViaRole()` can be used to check if a user has a role that has a permission.
## Description
```php
hasPermissionViaRole(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
```
## Arguments
- **$permission**
    - Type : `int` | `string` | `array` | `\Oslllo\Larakey\Contracts\Permission`
    - Description : The permission to give to the user.
- **$model**
    - Type : `string` | `\Illuminate\Database\Eloquent\Model`
    - Description : The model class or instance to be used with the permission to limit scope.
- **$modelId**
    - Type : `string` | `int`
    - Description : Used to indicate the id of a model when only a class name string is provided to `$model`. 
    - Note : ***`$model` must be present when this value is used.***
- **$guard**
    - Type : `string`
    - Description : The guard to be used with `$permission`. 
## Returns
    Returns boolean.
## Examples
> You can read about roles [here](#using-roles)
```php
$role = Role::create(['name' => 'writer']);

$role->givePermissionTo(['create', 'edit', 'delete'], Article::class);

$user->hasPermissionViaRole('edit', Article::class); // FALSE

$user->assignRole('writer');

$user->hasPermissionViaRole('edit', Article::class); // TRUE
```

---
