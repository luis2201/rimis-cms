<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleManagementAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_only_administrator_can_open_role_management(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');

        $this->actingAs($administrator)->get(route('admin.roles.index'))
            ->assertOk()->assertSee('Roles y permisos')->assertSee('WEBMASTER');
        $this->actingAs($webmaster)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_administrator_can_assign_permissions_to_a_non_protected_role(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = Role::findByName('WEBMASTER');

        $this->actingAs($administrator)->put(route('admin.roles.update', $webmaster), [
            'permissions' => ['dashboard.view', 'subscriptions.view', 'subscriptions.edit'],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'subscriptions.view', 'subscriptions.edit'],
            $webmaster->fresh()->permissions->pluck('name')->all()
        );
    }

    public function test_administrator_role_permissions_cannot_be_modified(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $role = Role::findByName('ADMINISTRADOR');
        $originalPermissions = $role->permissions->pluck('name')->all();

        $this->actingAs($administrator)->put(route('admin.roles.update', $role), [
            'permissions' => ['dashboard.view'],
        ])->assertForbidden();

        $this->assertEqualsCanonicalizing($originalPermissions, $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_seeder_does_not_overwrite_permissions_configured_from_the_panel(): void
    {
        $webmaster = Role::findByName('WEBMASTER');
        $webmaster->syncPermissions(['dashboard.view', 'subscriptions.view']);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['dashboard.view', 'subscriptions.view'],
            $webmaster->fresh()->permissions->pluck('name')->all()
        );
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
