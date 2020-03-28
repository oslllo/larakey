
# <u>Get Direct Permissions</u>

 > The function `getDirectPermissions()` can be used to return all direct permissions coupled to the model.

## Description

```php
getDirectPermissions(): \Illuminate\Support\Collection
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
$user->getDirectPermissions(); // OR

$role = Role::find(1);
$role->getDirectPermissions();
```

---
