<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearcherProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    public function test_incomplete_researcher_is_redirected_to_profile_from_dashboard(): void
    {
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($researcher)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('warning');
    }

    public function test_researcher_can_complete_professional_profile_with_private_pdf(): void
    {
        $researcher = $this->userWithRole('INVESTIGADOR');

        $response = $this->actingAs($researcher)->put(
            route('profile.researcher.update'),
            $this->researcherData()
        );

        $response->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $profile = $researcher->fresh()->researcherProfile;
        $this->assertNotNull($profile->completed_at);
        $this->assertSame('Ecuador', $profile->country);
        Storage::disk('local')->assertExists($profile->cv_path);

        $this->actingAs($researcher)->get(route('dashboard'))->assertOk();
    }

    public function test_curriculum_must_be_a_pdf(): void
    {
        $researcher = $this->userWithRole('INVESTIGADOR');
        $data = $this->researcherData();
        $data['cv'] = UploadedFile::fake()->image('photo.jpg');

        $this->actingAs($researcher)
            ->put(route('profile.researcher.update'), $data)
            ->assertSessionHasErrors('cv');
    }

    public function test_only_owner_or_administrator_can_download_curriculum(): void
    {
        $researcher = $this->userWithRole('INVESTIGADOR');
        $otherResearcher = $this->userWithRole('INVESTIGADOR');
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($researcher)->put(route('profile.researcher.update'), $this->researcherData());

        $this->actingAs($researcher)->get(route('profile.cv.download', $researcher))->assertOk();
        $this->actingAs($administrator)->get(route('profile.cv.download', $researcher))->assertOk();
        $this->actingAs($otherResearcher)->get(route('profile.cv.download', $researcher))->assertForbidden();
    }

    private function researcherData(): array
    {
        return [
            'country' => 'Ecuador',
            'salutation' => 'Doctora',
            'academic_title' => 'PhD',
            'profession' => 'Investigadora',
            'research_area' => 'Ciencias sociales y humanidades',
            'institution' => 'Universidad RIMIS',
            'phone' => '+593 999 999 999',
            'cv' => UploadedFile::fake()->create('curriculum.pdf', 100, 'application/pdf'),
        ];
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
