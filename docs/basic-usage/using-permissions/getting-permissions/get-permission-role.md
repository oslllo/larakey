# <u>Get Permission Role</u>

 > The function `getPermissionRole()` can be used to the first permission role. Returns new `Role` instance if none are found.

## Description

```php
getPermissionRole(mixed $permission): \Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role
```

### Arguments

- ***$permission***
    - Type : `\Oslllo\Larakey\Contracts\Permission|\Oslllo\Larakey\Models\Permission`
    - Description : The permission to use.

#### Returns

Returns `\Oslllo\Larakey\Contracts\Role|\Oslllo\Larakey\Models\Role`.

## Examples

```php
$permission = Permission::find(1);

$user->getPermissionRole($permission);
```

---
