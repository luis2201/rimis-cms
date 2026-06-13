<?php

namespace Tests\Feature\Authorization;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_authorized_roles_can_access_the_expected_event_actions(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.events.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.events.create'))->assertOk();
        $this->actingAs($researcher)->get(route('admin.events.index'))->assertOk();
        $this->actingAs($researcher)->get(route('admin.events.create'))->assertForbidden();
    }

    public function test_event_can_be_created_as_draft_with_information_and_safe_description(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.events.store'), [
            'title' => 'Encuentro de investigación',
            'summary' => 'Un espacio para compartir conocimiento.',
            'description' => '<p>Información del evento</p><script>alert(1)</script>',
            'starts_at' => '2026-07-15 09:00',
            'ends_at' => '2026-07-15 13:00',
            'modality' => Event::MODALITY_HYBRID,
            'location' => 'Auditorio principal y Zoom',
            'organizer' => 'RIMIS',
            'responsible_name' => 'Coordinación académica',
            'contact_email' => 'eventos@example.com',
            'contact_phone' => '+593 999 999 999',
            'website_url' => 'https://example.com/evento',
        ])->assertRedirect();

        $event = Event::firstOrFail();
        $this->assertSame(Event::STATUS_DRAFT, $event->status);
        $this->assertSame('encuentro-de-investigacion', $event->slug);
        $this->assertSame(Event::MODALITY_HYBRID, $event->modality);
        $this->assertStringNotContainsString('<script>', $event->description);
    }

    public function test_event_end_must_not_be_before_its_start(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.events.store'), [
            'title' => 'Evento inválido',
            'description' => 'Descripción',
            'starts_at' => '2026-07-15 13:00',
            'ends_at' => '2026-07-15 09:00',
            'modality' => Event::MODALITY_IN_PERSON,
        ])->assertSessionHasErrors('ends_at');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_only_published_events_are_public_and_listed_in_sitemap(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $event = $this->draftEvent($administrator);

        $this->get(route('events.show', $event->slug))->assertNotFound();
        $this->actingAs($administrator)->patch(route('admin.events.publish', $event))->assertRedirect();

        $this->get(route('events.index'))->assertOk()->assertSee($event->title);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee($event->organizer);
        $this->get(route('seo.sitemap'))->assertOk()->assertSee(route('events.show', $event->slug), false);
    }

    private function draftEvent(User $author): Event
    {
        return Event::create([
            'user_id' => $author->id,
            'title' => 'Seminario científico',
            'slug' => 'seminario-cientifico',
            'description' => '<p>Información completa.</p>',
            'starts_at' => '2026-07-15 09:00',
            'ends_at' => '2026-07-15 13:00',
            'modality' => Event::MODALITY_IN_PERSON,
            'location' => 'Auditorio RIMIS',
            'organizer' => 'RIMIS',
            'status' => Event::STATUS_DRAFT,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
