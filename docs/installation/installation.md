# Installation

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

6. NOTE: If you are using UUIDs, see the [UUID](#uuid) section under [Advanced](#advanced) of the docs on steps before you continue. It explains some changes you may want to make to the migrations and config file before continuing. It also mentions important considerations after extending this package’s models for UUID capability.

7. Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:

    ```bash
    php artisan migrate
    ```

8. Add the necessary trait to your ```User``` model: Consult the [Basic Usage](#basic-usage) section of the docs for how to get started using the features of this package.

---
