# Checking For Any Role
 > The function `hasAnyRole()` can be used to check if a user has any of the given roles.
## Description
```php
hasAnyRole(mixed $roles): bool
```
## Arguments
- **$roles**
    - Type : `array` | `string` | `int` | `\Oslllo\Larakey\Contracts\Role` | `\Illuminate\Support\Collection`
    - Description : The roles to check.
## Returns
    Returns bool.
## Usage
```php
$user->hasAnyRole(['writer', 'reader']);
// or
$user->hasAnyRole('writer', 'reader');
```

---
