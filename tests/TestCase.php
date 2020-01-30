<?php

namespace Ghustavh97\Guardian\Test;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Ghustavh97\Guardian\Contracts\Role;
use Illuminate\Database\Schema\Blueprint;
use Ghustavh97\Guardian\Test\Models\Post;
use Ghustavh97\Guardian\Test\Models\User;
use Ghustavh97\Guardian\Test\Models\Admin;
use Ghustavh97\Guardian\GuardianRegistrar;
use Ghustavh97\Guardian\Contracts\Permission;
use Orchestra\Testbench\TestCase as Orchestra;
use Ghustavh97\Guardian\GuardianServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var \Ghustavh97\Guardian\Test\Models\User */
    protected $testUser;

    /** @var \Ghustavh97\Guardian\Test\Models\Post */
    protected $testUserPost;

    /** @var \Ghustavh97\Guardian\Test\Models\Admin */
    protected $testAdmin;

    /** @var \Ghustavh97\Guardian\Models\Role */
    protected $testUserRole;

    /** @var \Ghustavh97\Guardian\Models\Role */
    protected $testAdminRole;

    /** @var \Ghustavh97\Guardian\Models\Permission */
    protected $testUserPermission;

    /** @var \Ghustavh97\Guardian\Models\Permission */
    protected $testAdminPermission;

    public function setUp(): void
    {
        parent::setUp();

        // Note: this also flushes the cache from within the migration
        $this->setUpDatabase($this->app);

        $this->testUser = User::email('testUser@test.com')->first();
        $this->testUserPost = $this->testUser->posts()->create(['title' => 'Test Title', 'description' => 'Test description']);
        $this->testUserRole = app(Role::class)->findByName('testUserRole');
        // TODO: A test fails if permission is not ->first()
        $this->testUserPermission = app(Permission::class)->where(['name' => 'edit-articles'])->first();
        // dd($this->testUserPermission);

        $this->testAdmin = Admin::email('testAdmin@test.com')->first();
        $this->testAdminRole = app(Role::class)->findByName('testAdminRole', 'admin');
        $this->testAdminPermission = app(Permission::class)->where(['name' => 'admin-permission', 'guard_name' => 'admin'])->first();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GuardianServiceProvider::class,
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

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);

        // Set-up admin guard
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $app['config']->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('cache.prefix', 'guardian_tests---');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['config']->set('guardian.column_names.model_morph_key', 'model_id');

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
            $app[GuardianRegistrar::class]->getCacheStore() instanceof \Illuminate\Cache\DatabaseStore) {
            $this->createCacheTable();
        }

        include_once __DIR__.'/../database/migrations/create_guardian_permission_tables.php.stub';

        (new \CreateGuardianPermissionTables())->up();

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

        // $app[Permission::class]->create(['name' => 'edit-any-post']);
        // $app[Permission::class]->create(['name' => 'edit-any-post']);
        // $app[Permission::class]->create(['name' => 'create-post']);
        // $app[Permission::class]->create(['name' => 'delete-post']);
        // $app[Permission::class]->create(['name' => 'edit-post']);
        // $app[Permission::class]->create(['name' => 'view-post']);

        $app[Permission::class]->create(['name' => 'create-comment']);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(GuardianRegistrar::class)->forgetCachedPermissions();
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
