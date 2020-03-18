# Checking For Roles
 > The function `hasRole()` can be used to check if a user has a role(s).
## Description
```php
hasRole(mixed $roles, [string $guard = null, bool $returnRole = false])
```
## Arguments
- **$roles**
    - Type : `string` | `int` | `array` | `\Oslllo\Larakey\Contracts\Role|\Illuminate\Support\Collection`
    - Description : The role(s) to check.
- **$guard**
    - Type : `string`
    - Description : Guard to be used wit roles.
- **$returnRole**
    - Type : `bool`
    - Description : Return first matching role from user.

## Returns
    Returns `bool` or `\Oslllo\Larakey\Contracts\Role`.
## Examples
```php
$user->hasRole('writer');
// or at least one role from an array of roles:
$user->hasRole(['editor', 'moderator']);
```

---
