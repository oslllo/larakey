# Cache

* [Cache](advanced-usage/cache/cache.md)
* [Manual Cache Reset](advanced-usage/cache/manual-cache-reset.md)
* [Cache Identifier](advanced-usage/cache/cache-identifier.md)

Role and Permission data are cached to speed up performance.

While we recommend not changing the cache "key" name, if you wish to alter the expiration time you may do so in the `config/larakey.php` file, in the `cache` array.

When you use the built-in functions for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record:

```php
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

---

# <u>Manual Cache Reset</u>
To manually reset the cache for this package, you can run the following in your app code:
```php
app()->make(\Oslllo\Larakey\Padlock\Cache::class)->forgetCachedPermissions();
```

Or you can use an Artisan command:
```bash
php artisan permission:cache-reset
```

---

# <u>Cache Identifier</u>

TIP: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites 
running on your server, you could run into cache clashes between apps. It is prudent to set your own 
cache `prefix` in Laravel's `/config/cache.php` to something unique for each application. 
This will prevent other applications from accidentally using/changing your cached data.

---
