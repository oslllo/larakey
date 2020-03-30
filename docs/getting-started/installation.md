# Installation

- [Prerequisites](#prerequisites)
- [Installation Process](#installation-process)
- [Default Config File Contents](#default-config-file-contents)

## Prerequisites

This package can be used in Laravel 5.8 or higher.

This package uses Laravel’s Gate layer to provide Authorization capabilities. The ```Gate/authorization``` layer requires that your ```User``` model implement the ```Illuminate\Contracts\Auth\Access\Authorizable``` contract. Otherwise the ```can()``` and ```authorize()``` methods will not work in your controllers, policies, templates, etc.

In the Installation instructions you’ll see that the ```HasLarakey``` trait must be added to the ```User``` model to enable this package’s features.

Thus, a typical basic ```User``` model would have these basic minimum requirements:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Oslllo\Larakey\Traits\HasLarakey;

class User extends Authenticatable
{
    use HasLarakey;

    // ...
}
```

Additionally, your ```User``` model/object 🚫**MUST NOT** have a role or roles property (or field in the database), nor a ```roles()``` method on it. Those will interfere with the properties and methods added by the ```HasLarakey``` trait provided by this package, thus causing unexpected outcomes when this package’s methods are used to inspect roles and permissions.

Similarly, your ```User``` model/object 🚫**MUST NOT** have a permission or permissions property (or field in the database), nor a ```permissions()``` method on it. Those will interfere with the properties and methods added by the ```HasPermissions``` trait provided by this package (which is invoked via the ```HasLarakey``` trait).

This package publishes a ```config/larakey.php``` file. If you already have a file by that name, you must rename or remove it, as it will conflict with this package. You could optionally merge your own values with those required by this package, as long as the keys that this package expects are present. See the [source](https://github.com/Oslllo/larakey/blob/master/config/larakey.php) file for more details.

---

## Installation Process

This package can be used with Laravel 5.8 or higher.

1. Consult the [Prerequisites](#prerequisites) page for important considerations regarding your ```User``` models!

2. This package publishes a ```config/larakey.php``` file. If you already have a file by that name, you must rename or remove it.

3. You can install the package via composer: ```composer require oslllo/larakey```

4. Optional: The service provider will automatically get registered. Or you may manually add the service provider in your ```config/app.php``` file:

```php
'providers' => [
    // ...
    Oslllo\Larakey\LarakeyServiceProvider::class,
];
```

5. You should publish [the migration](https://github.com/Oslllo/larakey/blob/master/database/migrations/create_larakey_permission_tables.php.stub) and the ```config/larakey.php``` [config file](https://github.com/Oslllo/larakey/blob/master/config/larakey.php) with:

```bash
php artisan vendor:publish --provider="Oslllo\Larakey\LarakeyServiceProvider::class"
```

6. ⚠️ **NOTE:** If you are using UUIDs, see the [UUID](advanced-usage/uuid.md) section under **Advanced Usage** of the docs on steps before you continue. It explains some changes you may want to make to the migrations and config file before continuing. It also mentions important considerations after extending this package’s models for UUID capability.

7. Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:

    ```bash
    php artisan migrate
    ```

8. Add the necessary trait to your ```User``` model: Consult the [Basic Usage](getting-started/basic-usage.md) section of the docs for how to get started using the features of this package.

---

## Default Config File Contents

You can view the default config file contents [HERE](https://github.com/Oslllo/larakey/blob/master/config/larakey.php)

---
