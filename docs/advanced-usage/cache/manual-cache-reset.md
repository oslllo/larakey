
# Manual cache reset
To manually reset the cache for this package, you can run the following in your app code:
```php
app()->make(\Oslllo\Larakey\Padlock\Cache::class)->forgetCachedPermissions();
```

Or you can use an Artisan command:
```bash
php artisan permission:cache-reset
```

---
