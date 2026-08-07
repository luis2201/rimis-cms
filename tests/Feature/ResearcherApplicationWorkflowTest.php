<?php

namespace Tests\Feature;

use App\Models\ResearcherApplication;
use App\Models\ResearcherProfile;
use App\Models\User;
use App\Notifications\ResearcherApplicationStatusNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearcherApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        Notification::fake();
    }

    public function test_user_can_create_one_draft_and_edit_it(): void
    {
        $user = $this->user('USUARIO');
        $this->actingAs($user)->post(route('applications.store'), ['motivation' => 'Inicial'])->assertRedirect(route('applications.show'));
        $application = $user->fresh()->researcherApplication;
        $this->assertTrue($application->isDraft());
        $this->assertCount(1, $application->history);
        $this->actingAs($user)->get(route('applications.show'))->assertOk()->assertSee('Mi postulación RIMIS');

        $this->actingAs($user)->put(route('applications.update'), $this->applicationData('Actualizada'))->assertRedirect(route('applications.show'));
        $this->assertSame('Actualizada', $application->fresh()->motivation);
        $this->actingAs($user)->post(route('applications.store'), [])->assertStatus(409);
    }

    public function test_submit_requires_complete_profile_and_creates_snapshot_and_history(): void
    {
        $user = $this->user('USUARIO');
        $application = $this->draft($user);

        $this->actingAs($user)->post(route('applications.submit'))->assertRedirect(route('profile.edit'));
        $this->assertTrue($application->fresh()->isDraft());

        $this->completeProfile($user);
        $this->actingAs($user)->post(route('applications.submit'))->assertRedirect(route('applications.show'));
        $application->refresh();
        $this->assertTrue($application->isSubmitted());
        $this->assertSame($user->email, $application->profile_snapshot['email']);
        $this->assertSame('Universidad RIMIS', $application->profile_snapshot['institution']);
        $this->assertCount(1, $application->history);
        Notification::assertSentTo($user, ResearcherApplicationStatusNotification::class);
        $this->actingAs($user)->get(route('applications.edit'))->assertForbidden();
    }

    public function test_profile_without_existing_cv_cannot_be_submitted(): void
    {
        $user = $this->user('USUARIO');
        $application = $this->draft($user);
        $this->completeProfile($user, false);

        $this->actingAs($user)->post(route('applications.submit'))->assertRedirect(route('profile.edit'));
        $this->assertTrue($application->fresh()->isDraft());
    }

    public function test_user_can_withdraw_only_in_allowed_states_and_cannot_view_others(): void
    {
        $owner = $this->user('USUARIO');
        $other = $this->user('USUARIO');
        $application = $this->draft($owner);
        $this->assertFalse(Gate::forUser($other)->allows('view', $application));

        $this->actingAs($owner)->post(route('applications.withdraw'))->assertRedirect(route('applications.show'));
        $this->assertTrue($application->fresh()->isWithdrawn());
        $this->actingAs($owner)->post(route('applications.withdraw'))->assertForbidden();
    }

    public function test_webmaster_can_review_observe_and_user_can_correct_and_resubmit(): void
    {
        $user = $this->submittedApplicationOwner();
        $application = $user->researcherApplication;
        $webmaster = $this->user('WEBMASTER');

        $this->actingAs($webmaster)->get(route('admin.researcher-applications.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.researcher-applications.show', $application))->assertOk();
        $this->actingAs($webmaster)->patch(route('admin.researcher-applications.start-review', $application))->assertRedirect();
        $this->actingAs($webmaster)->patch(route('admin.researcher-applications.observe', $application), ['review_notes' => 'Amplía tu experiencia.'])->assertRedirect();
        $this->assertTrue($application->fresh()->isObserved());

        $oldSnapshot = $application->profile_snapshot;
        $user->researcherProfile->update(['institution' => 'Nueva institución']);
        $this->actingAs($user)->put(route('applications.update'), $this->applicationData('Corregida'))->assertRedirect();
        $this->actingAs($user)->post(route('applications.submit'))->assertRedirect(route('applications.show'));
        $application->refresh();
        $this->assertTrue($application->isSubmitted());
        $this->assertNotSame($oldSnapshot['institution'], $application->profile_snapshot['institution']);
        $this->assertSame('Nueva institución', $application->profile_snapshot['institution']);
    }

    public function test_observe_and_reject_require_notes_and_rejection_keeps_user_role(): void
    {
        $user = $this->submittedApplicationOwner();
        $application = $user->researcherApplication;
        $admin = $this->user('ADMINISTRADOR');
        $this->actingAs($admin)->patch(route('admin.researcher-applications.start-review', $application));

        $this->actingAs($admin)->patch(route('admin.researcher-applications.observe', $application), [])->assertSessionHasErrors('review_notes');
        $this->actingAs($admin)->patch(route('admin.researcher-applications.reject', $application), [])->assertSessionHasErrors('review_notes');
        $this->actingAs($admin)->patch(route('admin.researcher-applications.reject', $application), ['review_notes' => 'No cumple los criterios.'])->assertRedirect();
        $this->assertTrue($application->fresh()->isRejected());
        $this->assertTrue($user->fresh()->hasRole('USUARIO'));
        $this->assertFalse($user->hasRole('INVESTIGADOR'));
        $this->actingAs($user)->get(route('applications.edit'))->assertForbidden();
    }

    public function test_approval_changes_membership_preserves_other_roles_and_is_idempotent(): void
    {
        $user = $this->submittedApplicationOwner();
        $user->assignRole('WEBMASTER');
        $application = $user->researcherApplication;
        $admin = $this->user('ADMINISTRADOR');
        $this->actingAs($admin)->patch(route('admin.researcher-applications.start-review', $application));
        $this->actingAs($admin)->patch(route('admin.researcher-applications.approve', $application), ['review_notes' => 'Aprobada.'])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.researcher-applications.approve', $application))->assertRedirect();

        $user->refresh(); $application->refresh();
        $this->assertTrue($application->isApproved());
        $this->assertTrue($user->hasRole('INVESTIGADOR'));
        $this->assertTrue($user->hasRole('WEBMASTER'));
        $this->assertFalse($user->hasRole('USUARIO'));
        $this->assertSame($admin->id, $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);
        $this->assertSame(3, $application->history()->count());
    }

    public function test_reviewer_cannot_review_own_application_and_invalid_transition_returns_conflict(): void
    {
        $webmaster = $this->user('WEBMASTER');
        $webmaster->givePermissionTo(['applications.create', 'applications.view-own', 'applications.submit']);
        $application = $this->draft($webmaster);
        $application->update(['status' => ResearcherApplication::STATUS_SUBMITTED]);
        $this->actingAs($webmaster)->patch(route('admin.researcher-applications.start-review', $application))->assertForbidden();

        $admin = $this->user('ADMINISTRADOR');
        $application->update(['status' => ResearcherApplication::STATUS_DRAFT]);
        $this->actingAs($admin)->patch(route('admin.researcher-applications.start-review', $application))->assertStatus(409);
    }

    public function test_reviewer_can_download_cv(): void
    {
        $user = $this->submittedApplicationOwner();
        $admin = $this->user('ADMINISTRADOR');
        $this->actingAs($admin)->get(route('admin.researcher-applications.cv', $user->researcherApplication))->assertOk();
    }

    public function test_approved_researcher_can_download_personalized_certificate(): void
    {
        $user = $this->submittedApplicationOwner();
        $application = $user->researcherApplication;
        $admin = $this->user('ADMINISTRADOR');

        $this->actingAs($admin)->patch(route('admin.researcher-applications.start-review', $application));
        $this->actingAs($admin)->patch(route('admin.researcher-applications.approve', $application));

        $this->actingAs($user->fresh())
            ->get(route('applications.show'))
            ->assertOk()
            ->assertSee('Descargar certificación RIMIS')
            ->assertSee(route('applications.certificate'), false);

        $response = $this->actingAs($user->fresh())->get(route('applications.certificate'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $cacheControl = $response->headers->get('cache-control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('certificacion-rimis-', $response->headers->get('content-disposition'));
    }

    public function test_certificate_is_forbidden_before_application_approval(): void
    {
        $user = $this->submittedApplicationOwner();
        $user->removeRole('USUARIO');
        $user->assignRole('INVESTIGADOR');

        $this->actingAs($user->fresh())
            ->get(route('applications.certificate'))
            ->assertForbidden();
    }

    private function submittedApplicationOwner(): User
    {
        $user = $this->user('USUARIO'); $this->completeProfile($user); $application = $this->draft($user);
        $this->actingAs($user)->post(route('applications.submit'));
        return $user->fresh();
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(); $user->assignRole($role); return $user;
    }

    private function draft(User $user): ResearcherApplication
    {
        return $user->researcherApplication()->create($this->applicationData() + ['status' => ResearcherApplication::STATUS_DRAFT]);
    }

    private function applicationData(string $motivation = 'Quiero colaborar'): array
    {
        return ['motivation' => $motivation, 'experience_summary' => 'Experiencia científica suficiente.', 'expected_contribution' => 'Contribuir con proyectos multidisciplinarios.'];
    }

    private function completeProfile(User $user, bool $storeCv = true): ResearcherProfile
    {
        if ($storeCv) { Storage::disk('local')->put('curricula/test.pdf', '%PDF'); }
        return $user->researcherProfile()->create(['country' => 'Ecuador', 'salutation' => 'Doctor', 'academic_title' => 'PhD', 'profession' => 'Investigador', 'research_area' => 'Ciencias sociales y humanidades', 'institution' => 'Universidad RIMIS', 'phone' => '0999999999', 'cv_path' => 'curricula/test.pdf', 'cv_original_name' => 'cv.pdf', 'completed_at' => now()]);
    }
}
