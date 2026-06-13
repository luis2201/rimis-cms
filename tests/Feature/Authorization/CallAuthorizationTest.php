<?php

namespace Tests\Feature\Authorization;

use App\Models\CallForProposal;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CallAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_authorized_roles_can_access_expected_call_actions(): void
    {
        $this->actingAs($this->userWithRole('ADMINISTRADOR'))->get(route('admin.calls.index'))->assertOk();
        $this->actingAs($this->userWithRole('WEBMASTER'))->get(route('admin.calls.create'))->assertOk();
        $this->actingAs($this->userWithRole('INVESTIGADOR'))->get(route('admin.calls.index'))->assertOk();
        $this->actingAs($this->userWithRole('INVESTIGADOR'))->get(route('admin.calls.create'))->assertForbidden();
    }

    public function test_call_requires_bases_and_valid_dates_and_starts_as_draft(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.calls.store'), [
            'title' => 'Fondos para investigación',
            'description' => '<p>Información segura</p><script>alert(1)</script>',
            'opens_at' => now()->subDay()->format('Y-m-d H:i'),
            'closes_at' => now()->addMonth()->format('Y-m-d H:i'),
            'bases_pdf' => UploadedFile::fake()->create('bases.pdf', 400, 'application/pdf'),
            'registration_enabled' => true,
            'registration_url' => 'https://example.com/inscripcion',
        ])->assertRedirect();

        $call = CallForProposal::firstOrFail();
        $this->assertSame(CallForProposal::STATUS_DRAFT, $call->status);
        $this->assertTrue($call->registration_enabled);
        $this->assertStringNotContainsString('<script>', $call->description);
        Storage::disk('local')->assertExists($call->bases_pdf_path);

        $this->actingAs($administrator)->post(route('admin.calls.store'), [
            'title' => 'Fechas inválidas',
            'description' => 'Descripción',
            'opens_at' => '2026-08-20 12:00',
            'closes_at' => '2026-08-19 12:00',
            'bases_pdf' => UploadedFile::fake()->create('bases.pdf', 400, 'application/pdf'),
            'registration_enabled' => false,
        ])->assertSessionHasErrors('closes_at');
    }

    public function test_published_open_call_is_public_downloadable_and_accepts_registrations(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $call = $this->draftCall($administrator);

        $this->get(route('calls.show', $call->slug))->assertNotFound();
        $this->get(route('calls.download', $call))->assertNotFound();
        $this->actingAs($administrator)->patch(route('admin.calls.publish', $call))->assertRedirect();

        $this->get(route('calls.index'))->assertOk()->assertSee($call->title);
        $this->get(route('calls.show', $call->slug))->assertOk()->assertSee('Inscribirse');
        $this->get(route('calls.download', $call))->assertOk();
        $this->get(route('seo.sitemap'))->assertOk()->assertSee(route('calls.show', $call->slug), false);
        $this->assertTrue($call->fresh()->acceptsRegistrations());
    }

    public function test_bases_can_be_replaced_and_are_deleted_with_call(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $call = $this->draftCall($administrator);
        $oldPath = $call->bases_pdf_path;

        $this->actingAs($administrator)->put(route('admin.calls.update', $call), [
            'title' => $call->title,
            'slug' => $call->slug,
            'description' => $call->description,
            'opens_at' => $call->opens_at->format('Y-m-d H:i'),
            'closes_at' => $call->closes_at->format('Y-m-d H:i'),
            'bases_pdf' => UploadedFile::fake()->create('nuevas-bases.pdf', 500, 'application/pdf'),
            'registration_enabled' => false,
        ])->assertRedirect();

        Storage::disk('local')->assertMissing($oldPath);
        $newPath = $call->fresh()->bases_pdf_path;
        Storage::disk('local')->assertExists($newPath);

        $this->actingAs($administrator)->delete(route('admin.calls.destroy', $call))->assertRedirect();
        Storage::disk('local')->assertMissing($newPath);
    }

    private function draftCall(User $author): CallForProposal
    {
        $file = UploadedFile::fake()->create('bases.pdf', 400, 'application/pdf');

        return CallForProposal::create([
            'user_id' => $author->id,
            'title' => 'Convocatoria científica',
            'slug' => 'convocatoria-cientifica',
            'description' => '<p>Detalles de la convocatoria.</p>',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addMonth(),
            'bases_pdf_path' => $file->store('calls', 'local'),
            'bases_pdf_original_name' => 'bases.pdf',
            'bases_pdf_size' => $file->getSize(),
            'registration_enabled' => true,
            'registration_url' => 'https://example.com/inscripcion',
            'status' => CallForProposal::STATUS_DRAFT,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
