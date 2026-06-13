<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    public function test_only_administrator_can_access_user_management(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Crear usuario');

        $this->actingAs($webmaster)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($researcher)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_administrator_can_create_a_webmaster(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $response = $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Webmaster RIMIS',
            'email' => 'webmaster@rimis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'WEBMASTER',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $webmaster = User::where('email', 'webmaster@rimis.test')->firstOrFail();
        $this->assertTrue($webmaster->hasRole('WEBMASTER'));
    }

    public function test_administrator_must_complete_professional_data_when_creating_researcher(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Researcher RIMIS',
            'email' => 'researcher@rimis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'INVESTIGADOR',
        ])->assertSessionHasErrors(['country', 'salutation', 'academic_title', 'profession', 'research_area', 'institution', 'phone', 'cv']);

        $response = $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Researcher RIMIS',
            'email' => 'researcher@rimis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'INVESTIGADOR',
            'country' => 'Ecuador',
            'salutation' => 'Señora',
            'academic_title' => 'MSc',
            'profession' => 'Investigadora',
            'research_area' => 'Ciencias de la educación',
            'institution' => 'Universidad RIMIS',
            'phone' => '+593 999 999 999',
            'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $researcher = User::where('email', 'researcher@rimis.test')->firstOrFail();
        $this->assertTrue($researcher->hasRole('INVESTIGADOR'));
        $this->assertTrue($researcher->hasCompleteResearcherProfile());
        Storage::disk('local')->assertExists($researcher->researcherProfile->cv_path);
    }

    public function test_administrator_cannot_assign_administrator_role_from_user_module(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.users.store'), [
            'name' => 'Other Admin',
            'email' => 'other-admin@rimis.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'ADMINISTRADOR',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'other-admin@rimis.test']);
    }

    public function test_administrator_can_update_an_assignable_user_role(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $user = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'WEBMASTER',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->hasRole('WEBMASTER'));
        $this->assertFalse($user->fresh()->hasRole('INVESTIGADOR'));
    }

    public function test_administrator_cannot_deactivate_self_or_another_administrator(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $otherAdministrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)
            ->patch(route('admin.users.deactivate', $administrator))
            ->assertForbidden();

        $this->actingAs($administrator)
            ->patch(route('admin.users.deactivate', $otherAdministrator))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
        $this->assertDatabaseHas('users', ['id' => $otherAdministrator->id]);
    }

    public function test_administrator_can_deactivate_and_reactivate_a_non_administrator(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)
            ->patch(route('admin.users.deactivate', $researcher))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $researcher->id,
            'is_active' => false,
        ]);

        $this->actingAs($administrator)
            ->patch(route('admin.users.activate', $researcher))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $researcher->id,
            'is_active' => true,
            'deactivated_at' => null,
        ]);
        $this->assertTrue($researcher->fresh()->hasRole('INVESTIGADOR'));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
