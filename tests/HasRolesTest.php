<?php

namespace Ghustavh97\Larakey\Test;

use Ghustavh97\Larakey\Contracts\Role;
use Ghustavh97\Larakey\Test\Models\User;
use Ghustavh97\Larakey\Test\Models\Admin;
use Ghustavh97\Larakey\Exceptions\RoleDoesNotExist;
use Ghustavh97\Larakey\Exceptions\GuardDoesNotMatch;

class HasRolesTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testUserRole'));

        $role = app(Role::class)->findOrCreate('testRoleInWebGuard', 'web');

        $this->assertFalse($this->testUser->hasRole($role));

        $this->testUser->assignRole($role);
        $this->assertTrue($this->testUser->hasRole($role));
        $this->assertTrue($this->testUser->hasRole($role->name));
        $this->assertTrue($this->testUser->hasRole($role->name, $role->guard_name));
        $this->assertTrue($this->testUser->hasRole([$role->name, 'fakeRole'], $role->guard_name));
        $this->assertTrue($this->testUser->hasRole($role->id, $role->guard_name));
        $this->assertTrue($this->testUser->hasRole([$role->id, 'fakeRole'], $role->guard_name));

        $this->assertFalse($this->testUser->hasRole($role->name, 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole([$role->name, 'fakeRole'], 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole($role->id, 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole([$role->id, 'fakeRole'], 'fakeGuard'));

        $role = app(Role::class)->findOrCreate('testRoleInWebGuard2', 'web');
        $this->assertFalse($this->testUser->hasRole($role));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testUserRole'));

        $this->testUser->assignRole('testUserRole');

        $this->assertTrue($this->testUser->hasRole('testUserRole'));

        $this->testUser->removeRole('testUserRole');

        $this->assertFalse($this->testUser->hasRole('testUserRole'));
    }

    /** @test */
    public function it_removes_a_role_and_returns_roles()
    {
        $this->testUser->assignRole('testUserRole');

        $this->testUser->assignRole('testUserRole2');

        $this->assertTrue($this->testUser->hasRole(['testUserRole', 'testUserRole2']));

        $roles = $this->testUser->removeRole('testUserRole');

        $this->assertFalse($roles->hasRole('testUserRole'));

        $this->assertTrue($roles->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_on_a_permission()
    {
        $this->testUserPermission->assignRole('testUserRole');

        $this->assertTrue($this->testUserPermission->hasRole('testUserRole'));

        $this->testUserPermission->removeRole('testUserRole');

        $this->assertFalse($this->testUserPermission->hasRole('testUserRole'));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_id()
    {
        $this->testUser->assignRole($this->testUserRole->id);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_multiple_roles_at_once()
    {
        $this->testUser->assignRole($this->testUserRole->id, 'testUserRole2');

        $this->assertTrue($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRole([$this->testUserRole->id, 'testUserRole2']);

        $this->assertTrue($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_does_not_remove_already_associated_roles_when_assigning_new_roles()
    {
        $this->testUser->assignRole($this->testUserRole->id);

        $this->testUser->assignRole('testUserRole2');

        $this->assertTrue($this->testUser->fresh()->hasRole('testUserRole'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_role_that_is_already_assigned()
    {
        $this->testUser->assignRole($this->testUserRole->id);

        $this->testUser->assignRole($this->testUserRole->id);

        $this->assertTrue($this->testUser->fresh()->hasRole('testUserRole'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor');
    }

    /** @test */
    public function it_can_only_assign_roles_from_the_correct_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('testAdminRole');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->assignRole($this->testAdminRole);
    }

    /** @test */
    public function it_ignores_null_roles_when_syncing()
    {
        $this->testUser->assignRole('testUserRole');

        $this->testUser->syncRoles('testUserRole2', null);

        $this->assertFalse($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_sync_roles_from_a_string()
    {
        $this->testUser->assignRole('testUserRole');

        $this->testUser->syncRoles('testUserRole2');

        $this->assertFalse($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_sync_roles_from_a_string_on_a_permission()
    {
        $this->testUserPermission->assignRole('testUserRole');

        $this->testUserPermission->syncRoles('testUserRole2');

        $this->assertFalse($this->testUserPermission->hasRole('testUserRole'));

        $this->assertTrue($this->testUserPermission->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles()
    {
        $this->testUser->syncRoles('testUserRole', 'testUserRole2');

        $this->assertTrue($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles_from_an_array()
    {
        $this->testUser->syncRoles(['testUserRole', 'testUserRole2']);

        $this->assertTrue($this->testUser->hasRole('testUserRole'));

        $this->assertTrue($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_will_remove_all_roles_when_an_empty_array_is_passed_to_sync_roles()
    {
        $this->testUser->assignRole('testUserRole');

        $this->testUser->assignRole('testUserRole2');

        $this->testUser->syncRoles([]);

        $this->assertFalse($this->testUser->hasRole('testUserRole'));

        $this->assertFalse($this->testUser->hasRole('testUserRole2'));
    }

    /** @test */
    public function it_will_sync_roles_to_a_model_that_is_not_persisted()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncRoles([$this->testUserRole]);
        $user->save();

        $this->assertTrue($user->hasRole($this->testUserRole));
    }

    /** @test */
    public function calling_syncRoles_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncRoles('testUserRole');
        $user->save();

        $user2 = new User(['email' => 'admin@user.com']);
        $user2->syncRoles('testUserRole2');
        $user2->save();

        $this->assertTrue($user2->fresh()->hasRole('testUserRole2'));
        $this->assertFalse($user2->fresh()->hasRole('testUserRole'));
    }

    /** @test */
    public function calling_assignRole_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->assignRole('testUserRole');
        $user->save();

        $admin_user = new User(['email' => 'admin@user.com']);
        $admin_user->assignRole('testUserRole2');
        $admin_user->save();

        $this->assertTrue($admin_user->fresh()->hasRole('testUserRole2'));
        $this->assertFalse($admin_user->fresh()->hasRole('testUserRole'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->syncRoles('testUserRole', 'testAdminRole');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->syncRoles('testUserRole', $this->testAdminRole);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user = User::create(['email' => 'user@test.com']);

        $user->assignRole('testUserRole');
        $user->givePermissionTo('edit-articles');

        $this->assertDatabaseHas(
            config('larakey.table_names.model_has_permissions'),
            [config('larakey.column_names.model_morph_key') => $user->id]
        );

        $this->assertDatabaseHas(
            config('larakey.table_names.model_has_roles'),
            [config('larakey.column_names.model_morph_key') => $user->id]
        );

        $user->delete();

        $this->assertDatabaseMissing(
            config('larakey.table_names.model_has_permissions'),
            [config('larakey.column_names.model_morph_key') => $user->id]
        );

        $this->assertDatabaseMissing(
            config('larakey.table_names.model_has_roles'),
            [config('larakey.column_names.model_morph_key') => $user->id]
        );
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testUserRole');
        $user2->assignRole('testUserRole2');

        $scopedUsers = User::role('testUserRole')->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testUserRole2');

        $scopedUsers1 = User::role([$this->testUserRole])->get();

        $scopedUsers2 = User::role(['testUserRole', 'testUserRole2'])->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_an_array_of_ids_and_names()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        $user1->assignRole($this->testUserRole);

        $user2->assignRole('testUserRole2');

        $roleName = $this->testUserRole->name;

        $otherRoleId = app(Role::class)->find(2)->id;

        $scopedUsers = User::role([$roleName, $otherRoleId])->get();

        $this->assertEquals($scopedUsers->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testUserRole2');

        $scopedUsers1 = User::role([$this->testUserRole])->get();
        $scopedUsers2 = User::role(collect(['testUserRole', 'testUserRole2']))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testUserRole2');

        $scopedUsers1 = User::role($this->testUserRole)->get();
        $scopedUsers2 = User::role([$this->testUserRole])->get();
        $scopedUsers3 = User::role(collect([$this->testUserRole]))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 1);
        $this->assertEquals($scopedUsers3->count(), 1);
    }

    /** @test */
    public function it_can_scope_against_a_specific_guard()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testUserRole');
        $user2->assignRole('testUserRole2');

        $scopedUsers1 = User::role('testUserRole', 'web')->get();

        $this->assertEquals($scopedUsers1->count(), 1);

        $user3 = Admin::create(['email' => 'user1@test.com']);
        $user4 = Admin::create(['email' => 'user1@test.com']);
        $user5 = Admin::create(['email' => 'user2@test.com']);
        $testAdminRole2 = app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']);
        $user3->assignRole($this->testAdminRole);
        $user4->assignRole($this->testAdminRole);
        $user5->assignRole($testAdminRole2);
        $scopedUsers2 = Admin::role('testAdminRole', 'admin')->get();
        $scopedUsers3 = Admin::role('testAdminRole2', 'admin')->get();

        $this->assertEquals($scopedUsers2->count(), 2);
        $this->assertEquals($scopedUsers3->count(), 1);
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        User::role('testAdminRole')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::role($this->testAdminRole)->get();
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_non_existing_role()
    {
        $this->expectException(RoleDoesNotExist::class);

        User::role('role not defined')->get();
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'second role']);

        $this->assertFalse($this->testUser->hasRole($roleModel->all()));

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole('testUserRole'));

        $this->assertFalse($this->testUser->hasAnyRole('role does not exist'));

        $this->assertTrue($this->testUser->hasAnyRole(['testUserRole']));

        $this->assertTrue($this->testUser->hasAnyRole(['testUserRole', 'role does not exist']));

        $this->assertFalse($this->testUser->hasAnyRole(['role does not exist']));

        $this->assertTrue($this->testUser->hasAnyRole('testUserRole', 'role does not exist'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->first()));

        $this->assertFalse($this->testUser->hasAllRoles('testUserRole'));

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->all()));

        $roleModel->create(['name' => 'second role']);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasAllRoles('testUserRole'));
        $this->assertTrue($this->testUser->hasAllRoles('testUserRole', 'web'));
        $this->assertFalse($this->testUser->hasAllRoles('testUserRole', 'fakeGuard'));

        $this->assertFalse($this->testUser->hasAllRoles(['testUserRole', 'second role']));
        $this->assertFalse($this->testUser->hasAllRoles(['testUserRole', 'second role'], 'web'));

        $this->testUser->assignRole('second role');

        $this->assertTrue($this->testUser->hasAllRoles(['testUserRole', 'second role']));
        $this->assertTrue($this->testUser->hasAllRoles(['testUserRole', 'second role'], 'web'));
        $this->assertFalse($this->testUser->hasAllRoles(['testUserRole', 'second role'], 'fakeGuard'));
    }

    /** @test */
    public function it_can_determine_that_a_user_does_not_have_a_role_from_another_guard()
    {
        $this->assertFalse($this->testUser->hasRole('testAdminRole'));

        $this->assertFalse($this->testUser->hasRole($this->testAdminRole));

        $this->testUser->assignRole('testUserRole');

        $this->assertTrue($this->testUser->hasAnyRole(['testUserRole', 'testAdminRole']));

        $this->assertFalse($this->testUser->hasAnyRole('testAdminRole', $this->testAdminRole));
    }

    /** @test */
    public function it_can_check_against_any_multiple_roles_using_multiple_arguments()
    {
        $this->testUser->assignRole('testUserRole');

        $this->assertTrue($this->testUser->hasAnyRole($this->testAdminRole, ['testUserRole'], 'This Role Does Not Even Exist'));
    }

    /** @test */
    public function it_returns_false_instead_of_an_exception_when_checking_against_any_undefined_roles_using_multiple_arguments()
    {
        $this->assertFalse($this->testUser->hasAnyRole('This Role Does Not Even Exist', $this->testAdminRole));
    }

    /** @test */
    public function it_can_retrieve_role_names()
    {
        $this->testUser->assignRole('testUserRole', 'testUserRole2');

        $this->assertEquals(
            collect(['testUserRole', 'testUserRole2']),
            $this->testUser->getRoleNames()
        );
    }

    /** @test */
    public function it_does_not_detach_roles_when_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->assignRole('testUserRole');
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasRole('testUserRole'));
    }
}
