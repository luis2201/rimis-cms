<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['dashboard.view', 'dashboard.researcher'] as $permission) {
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
}
