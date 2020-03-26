<?php

namespace Oslllo\Larakey\Test;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Oslllo\Larakey\Contracts\Role;
use Illuminate\Database\Schema\Blueprint;
use Oslllo\Larakey\Test\App\Models\Post;
use Oslllo\Larakey\Test\App\Models\User;
use Oslllo\Larakey\Test\App\Models\Admin;

use Oslllo\Larakey\Padlock\Cache as LaraCache;

use Oslllo\Larakey\Contracts\Permission;
use Orchestra\Testbench\TestCase as Orchestra;
use Oslllo\Larakey\LarakeyServiceProvider;
use Oslllo\Larakey\Test\App\Providers\RouteServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var \Oslllo\Larakey\Test\App\Models\User */
    protected $testUser;

    /** @var \Oslllo\Larakey\Test\App\Models\Post */
    protected $testUserPost;

    /** @var \Oslllo\Larakey\Test\App\Models\Admin */
    protected $testAdmin;

    /** @var \Oslllo\Larakey\Models\Role */
    protected $testUserRole;

    /** @var \Oslllo\Larakey\Models\Role */
    protected $testAdminRole;

    /** @var \Oslllo\Larakey\Models\Permission */
    protected $testUserPermission;

    /** @var \Oslllo\Larakey\Models\Permission */
    protected $testAdminPermission;

    public function setUp(): void
    {
        parent::setUp();

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);

        $this->testUser = User::email('testUser@test.com')->first();

        $this->testUserPost = $this->testUser->posts()->create([
            'title' => 'Test Title',
            'description' => 'Test description'
        ]);

        $this->testUserRole = app(Role::class)->where([
            'name' => 'testUserRole'
        ])
        ->first();

        // TODO: A test fails if permission is not ->first()
        $this->testUserPermission = app(Permission::class)->where([
            'name' => 'edit-articles'
        ])
        ->first();

        $this->testAdmin = Admin::email('testAdmin@test.com')->first();

        $this->testAdminRole = app(Role::class)->where([
            'name' => 'testAdminRole',
            'guard_name' => 'admin'
        ])
        ->first();

        $this->testAdminPermission = app(Permission::class)->where([
            'name' => 'admin-permission',
            'guard_name' => 'admin'
        ])
        ->first();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LarakeyServiceProvider::class,
            RouteServiceProvider::class
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/App/resources/views']);

        // Set-up admin guard
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $app['config']->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('cache.prefix', 'larakey_tests---');

        $app['config']->set('app.key', 'Idgz1PE3zO9iNc0E3oeH3CHDPX9MzZe3');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['config']->set('larakey.column_names.model_morph_key', 'model_id');

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('title');
            $table->text('description');
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('post_id');
            $table->text('description');
            $table->softDeletes();
        });

        if (Cache::getStore() instanceof \Illuminate\Cache\DatabaseStore ||
            $app[LaraCache::class]->getCacheStore() instanceof \Illuminate\Cache\DatabaseStore) {
            $this->createCacheTable();
        }

        include_once __DIR__.'/../database/migrations/create_larakey_permission_tables.php.stub';

        (new \CreateLarakeyPermissionTables())->up();

        User::create(['email' => 'testUser@test.com']);
        Admin::create(['email' => 'testAdmin@test.com']);

        $app[Role::class]->create(['name' => 'testUserRole']);
        $app[Role::class]->create(['name' => 'testUserRole2']);
        $app[Role::class]->create(['name' => 'testAdminRole', 'guard_name' => 'admin']);

        $app[Permission::class]->create(['name' => 'edit-articles']);
        $app[Permission::class]->create(['name' => 'edit-news']);
        $app[Permission::class]->create(['name' => 'edit-blog']);
        $app[Permission::class]->create(['name' => 'admin-permission', 'guard_name' => 'admin']);
        $app[Permission::class]->create(['name' => 'Edit News']);

        $app[Permission::class]->create(['name' => 'manage']);
        $app[Permission::class]->create(['name' => 'comment']);
        $app[Permission::class]->create(['name' => 'comment-on']);
        
        $app[Permission::class]->create(['name' => 'delete']);
        $app[Permission::class]->create(['name' => 'create']);
        $app[Permission::class]->create(['name' => 'view']);
        $app[Permission::class]->create(['name' => 'edit']);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(LaraCache::class)->forgetCachedPermissions();
    }

    public function createCacheTable()
    {
        Schema::create('cache', function ($table) {
            $table->string('key')->unique();
            $table->text('value');
            $table->integer('expiration');
        });
    }
}
