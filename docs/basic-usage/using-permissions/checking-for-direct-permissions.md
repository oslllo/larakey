# Checking For Direct Permissions
 > The function `hasDirectPermission()` can be used to check if a user has a direct permission.
## Description
```php
hasDirectPermission(mixed $permission, [mixed $model = null, [mixed $modelId = null]], [string $guard]): bool
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
```php
$post = Post::find(1);

$user->hasDirectPermission('edit');
$user->hasDirectPermission('edit', '*');
$user->hasDirectPermission('edit', $post);
$user->hasDirectPermission('edit', Post::class);
$user->hasDirectPermission('edit', Post::class, 1);
```
---
