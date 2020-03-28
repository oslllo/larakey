
# <u>Get Permissions Via Roles</u>

 > The function `getPermissionsViaRoles()` can be used to return all the permissions the model has via roles.

## Description

```php
getPermissionsViaRoles(): \Illuminate\Support\Collection
```

### Arguments

`none`

#### Returns

Returns `\Illuminate\Support\Collection`.

---

## Examples

```php
// Get direct permissions
$user = User::find(1);
$user->getPermissionsViaRoles();
```

---
