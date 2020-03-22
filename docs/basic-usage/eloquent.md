# <u>Eloquent</u>
Since ```Role``` and ```Permission``` models are extended from Eloquent models, basic Eloquent calls can be used as well:
```php
$all_users_with_all_their_roles = User::with('roles')->get();
$all_users_with_all_direct_permissions = User::with('permissions')->get();
$all_roles_in_database = Role::all()->pluck('name');
```

---
