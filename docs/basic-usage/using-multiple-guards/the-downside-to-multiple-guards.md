# <u>The Downside To Multiple Guards</u>

Note that this package requires you to register a permission name for each guard you want to authenticate with. So, "edit-article" would have to be created multiple times for each guard your app uses. An exception will be thrown if you try to authenticate against a non-existing permission+guard combination. Same for roles.

!> **Tip**: If your app uses only a single guard, but is not `web` (Laravel's default, which shows "first" in the auth config file) then change the order of your listed guards in your `config/auth.php` to list your primary guard as the default and as the first in the list of defined guards. While you're editing that file, best to remove any guards you don't use, too.