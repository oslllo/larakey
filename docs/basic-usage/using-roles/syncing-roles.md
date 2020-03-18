# Syncing Roles
 > The function `syncRoles()` can be used to sync roles on a user.
## Description
```php
syncRoles(mixed $roles): $this
```
## Arguments

- **$roles**
    - Type : `array` | `string` | `\Oslllo\Larakey\Contracts\Role`
    - Description : The roles to sync on user.

## Returns
    Returns $this.
## Examples
```php
// All current roles will be removed from the user and replaced by the array given
$user->syncRoles(['writer', 'admin']);
```

---
