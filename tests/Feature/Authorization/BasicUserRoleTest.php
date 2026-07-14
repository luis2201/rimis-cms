<?php

namespace Tests\Feature\Authorization;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BasicUserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_role_has_expected_basic_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $role = Role::findByName('USUARIO');

        $this->assertEqualsCanonicalizing([
            'dashboard.basic',
            'profile.view',
            'profile.edit',
            'applications.create',
            'applications.view-own',
            'applications.edit-own',
            'applications.submit',
            'applications.withdraw',
        ], $role->permissions->pluck('name')->all());
    }

    public function test_admin_keeps_all_permissions_and_webmaster_keeps_content_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertTrue(Role::findByName('ADMINISTRADOR')->hasPermissionTo('applications.submit'));
        $this->assertTrue(Role::findByName('ADMINISTRADOR')->hasPermissionTo('users.edit'));
        $this->assertTrue(Role::findByName('WEBMASTER')->hasPermissionTo('pages.edit'));
        $this->assertTrue(Role::findByName('INVESTIGADOR')->hasPermissionTo('dashboard.researcher'));
    }
}
