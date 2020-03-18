<?php

namespace Oslllo\Larakey\Test;

use Illuminate\Support\Facades\DB;
use Oslllo\Larakey\Contracts\Role;
use Illuminate\Support\Facades\Artisan;
use Oslllo\Larakey\Test\Models\User;
use Oslllo\Larakey\LarakeyRegistrar;
use Oslllo\Larakey\Contracts\Permission;
use Oslllo\Larakey\Exceptions\PermissionDoesNotExist;

use Oslllo\Larakey\Padlock\Cache;
use Oslllo\Larakey\Larakey;

class CacheTest extends TestCase
{
    protected $cache_init_count = 0;
    protected $cache_load_count = 0;
    protected $cache_permissions_run_count = 2;
    protected $cache_roles_run_count = 2;
    protected $cache_run_count; // roles lookup, permissions lookup,
    protected $cache_relations_count = 1;

    protected $registrar;

    public function setUp(): void
    {
        parent::setUp();

        $this->cache = app(Cache::class);

        $this->larakey = app(Larakey::class);

        $this->cache->forgetCachedPermissions();

        $this->cache->forgetCachedRoles();

        DB::connection()->enableQueryLog();

        $cacheStore = $this->cache->getCacheStore();

        $this->cache_run_count = $this->cache_permissions_run_count + $this->cache_roles_run_count;

        switch (true) {
            case $cacheStore instanceof \Illuminate\Cache\DatabaseStore:
                $this->cache_init_count = 1;
                $this->cache_load_count = 1;
            default:
        }
    }

    /** @test */
    public function it_can_cache_the_permissions()
    {
        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_permissions_run_count);
    }

    /** @test */
    public function it_can_cache_the_roles()
    {
        $this->resetQueryCount();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_roles_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_permission()
    {
        app(Permission::class)->create(['name' => 'new']);

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_permission()
    {
        $permission = app(Permission::class)->create(['name' => 'new']);

        $permission->name = 'other name';
        $permission->save();

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_role()
    {
        app(Role::class)->create(['name' => 'new']);

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_role()
    {
        $role = app(Role::class)->create(['name' => 'new']);

        $role->name = 'other name';
        $role->save();

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function it_flushes_the_cache_when_removing_a_role_from_a_user()
    {
        $this->testUser->assignRole('testUserRole');

        $this->cache->getCachedPermissions();

        $this->testUser->removeRole('testUserRole');

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function user_creation_should_not_flush_the_cache()
    {
        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        User::create(['email' => 'new']);

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        // should all be in memory, so no init/load required
        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_flushes_the_cache_when_giving_a_permission_to_a_role()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->resetQueryCount();

        $this->cache->getCachedPermissions();

        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    /** @test */
    public function has_permission_to_should_use_the_cache()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news', 'Edit News']);

        $this->testUser->assignRole('testUserRole');

        $this->resetQueryCount();

        $this->testUser->hasPermissionTo('edit-articles');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + $this->cache_relations_count);

        $this->resetQueryCount();
        $this->testUser->hasPermissionTo('edit-news');
        $this->assertQueryCount(0);

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertQueryCount(0);

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('Edit News'));
        $this->assertQueryCount(0);
    }

    /** @test */
    public function the_cache_should_differentiate_by_guard_name()
    {
        $this->expectException(PermissionDoesNotExist::class);
        // TODO: Array with permission and guard passes
        $this->testUserRole->givePermissionTo(['edit-articles'], 'web');
        $this->testUser->assignRole('testUserRole');

        $this->resetQueryCount();
        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles', 'web'));
        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count + $this->cache_relations_count);

        $this->resetQueryCount();
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles', 'admin'));
        $this->assertQueryCount(1); // 1 for first lookup of this permission with this guard
    }

    /** @test */
    public function get_all_permissions_should_use_the_cache()
    {
        $this->testUserRole->givePermissionTo($expected = ['edit-articles', 'edit-news']);
        $this->testUser->assignRole('testUserRole');

        $this->resetQueryCount();
        $this->cache->getCachedPermissions();
        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_permissions_run_count);

        $this->resetQueryCount();
        $actual = $this->testUser->getAllPermissions()->pluck('name')->sort()->values();
        $this->assertEquals($actual, collect($expected));
        
        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_reset_the_cache_with_artisan_command()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);
        $this->assertCount(1, \Oslllo\Larakey\Models\Permission::where('name', 'new-permission')->get());

        $this->resetQueryCount();
        // retrieve permissions and roles, and assert that the cache had to be loaded
        $this->cache->getCachedPermissions();
        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

        Artisan::call('permission:create-role', ['name' => 'new-role']);
        $this->assertCount(1, \Oslllo\Larakey\Models\Role::where('name', 'new-role')->get());

        $this->resetQueryCount();
        // retrieve permissions and roles, and assert that the cache had to be loaded
        $this->cache->getCachedPermissions();
        $this->cache->getCachedRoles();

        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);

        // reset the cache
        Artisan::call('permission:cache-reset');

        $this->resetQueryCount();
        $this->cache->getCachedPermissions();
        $this->cache->getCachedRoles();
        // assert that the cache had to be reloaded
        $this->assertQueryCount($this->cache_init_count + $this->cache_load_count + $this->cache_run_count);
    }

    protected function assertQueryCount(int $expected)
    {
        $this->assertCount($expected, DB::getQueryLog());
    }

    protected function resetQueryCount()
    {
        DB::flushQueryLog();
    }
}
