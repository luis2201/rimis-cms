<?php

namespace Tests\Feature;

use App\Models\ResearcherApplication;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrateExistingResearchersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_researcher_is_migrated_once_and_keeps_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $researcher = User::factory()->create();
        $researcher->assignRole('INVESTIGADOR');

        $this->artisan('rimis:migrate-existing-researchers')->assertSuccessful();
        $this->artisan('rimis:migrate-existing-researchers')->assertSuccessful();

        $researcher->refresh();
        $application = $researcher->researcherApplication;
        $this->assertTrue($researcher->hasRole('INVESTIGADOR'));
        $this->assertTrue($application->isApproved());
        $this->assertNotNull($application->submitted_at);
        $this->assertNotNull($application->reviewed_at);
        $this->assertSame('Membresía migrada desde el flujo anterior de RIMIS.', $application->review_notes);
        $this->assertCount(1, $application->history);
        $this->assertSame(ResearcherApplication::STATUS_APPROVED, $application->history->first()->new_status);
        $this->assertDatabaseCount('researcher_applications', 1);
        $this->assertDatabaseCount('researcher_application_history', 1);
    }
}
