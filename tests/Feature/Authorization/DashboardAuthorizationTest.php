<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['dashboard.view', 'dashboard.researcher', 'dashboard.basic'] as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    public function test_administrator_permission_displays_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard administrativo');
    }

    public function test_researcher_permission_displays_researcher_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.researcher');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel del investigador');
    }

    public function test_user_without_dashboard_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }

    public function test_verified_basic_user_displays_user_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.basic');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mi espacio RIMIS')
            ->assertSee('todavía no perteneces');
    }

    public function test_unverified_non_member_can_use_basic_dashboard(): void
    {
        Role::create(['name' => 'USUARIO', 'guard_name' => 'web'])->givePermissionTo('dashboard.basic');
        $user = User::factory()->unverified()->create();
        $user->assignRole('USUARIO');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_basic_user_cannot_access_admin_or_researcher_capabilities(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('dashboard.basic');

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->assertFalse($user->can('dashboard.researcher'));
        $this->assertFalse($user->can('research.view'));
    }
}
