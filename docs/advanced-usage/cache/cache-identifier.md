# Cache Identifier

TIP: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites 
running on your server, you could run into cache clashes between apps. It is prudent to set your own 
cache `prefix` in Laravel's `/config/cache.php` to something unique for each application. 
This will prevent other applications from accidentally using/changing your cached data.

---
