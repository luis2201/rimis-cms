<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearcherContentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(RolesAndPermissionsSeeder::class); Storage::fake('local');
    }

    public function test_researcher_creates_and_submits_event_with_private_attachment_and_history(): void
    {
        $researcher = $this->researcher();
        $this->actingAs($researcher)->post(route('researcher.submissions.event.store'), [
            'title'=>'Congreso RIMIS','description'=>'Descripción','starts_at'=>'2026-08-01 09:00','ends_at'=>'2026-08-01 12:00','modality'=>'hybrid',
            'attachment'=>UploadedFile::fake()->image('programa.jpg'),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $event = Event::first();
        $this->assertSame(Event::ORIGIN_RESEARCHER, $event->origin);
        $this->assertSame(Event::REVIEW_DRAFT, $event->review_status);
        Storage::disk('local')->assertExists($event->attachment_path);
        $this->actingAs($researcher)->post(route('researcher.submissions.event.submit', $event))->assertRedirect();
        $this->assertSame(Event::REVIEW_SUBMITTED, $event->fresh()->review_status);
        $this->assertDatabaseCount('content_review_history', 2);
    }

    public function test_researcher_creates_bulletin_and_call_reusing_private_pdf_fields(): void
    {
        $researcher = $this->researcher();
        $this->actingAs($researcher)->post(route('researcher.submissions.bulletin.store'), ['title'=>'Boletín científico','pdf'=>UploadedFile::fake()->create('boletin.pdf',100,'application/pdf')])->assertSessionHasNoErrors();
        $this->actingAs($researcher)->post(route('researcher.submissions.call.store'), ['title'=>'Fondos 2026','description'=>'Bases','opens_at'=>'2026-08-01','closes_at'=>'2026-09-01','registration_enabled'=>0,'bases_pdf'=>UploadedFile::fake()->create('bases.pdf',100,'application/pdf')])->assertSessionHasNoErrors();
        $this->assertStringStartsWith('researcher-submissions/bulletin/', Bulletin::first()->pdf_path);
        $this->assertStringStartsWith('researcher-submissions/call/', CallForProposal::first()->bases_pdf_path);
    }

    public function test_user_and_other_researcher_cannot_access_or_modify_submission(): void
    {
        $owner=$this->researcher(); $other=$this->researcher(); $user=User::factory()->create(); $user->assignRole('USUARIO');
        $event=$this->event($owner);
        $this->actingAs($user)->get(route('researcher.submissions.index'))->assertForbidden();
        $this->actingAs($other)->get(route('researcher.submissions.event.edit',$event))->assertForbidden();
        $this->actingAs($other)->get(route('researcher.submissions.event.download',$event))->assertForbidden();
    }

    public function test_researcher_content_is_public_only_after_review_approval_and_publication(): void
    {
        $event=$this->event($this->researcher());
        $event->update(['status'=>Event::STATUS_PUBLISHED,'published_at'=>now()]);
        $this->get(route('events.show',$event->slug))->assertNotFound();
        $event->update(['review_status'=>Event::REVIEW_APPROVED]);
        $this->get(route('events.show',$event->slug))->assertOk();
    }

    public function test_staff_content_keeps_not_required_review_and_existing_public_behavior(): void
    {
        $admin=User::factory()->create(); $admin->assignRole('ADMINISTRADOR');
        $this->actingAs($admin)->post(route('admin.events.store'), ['title'=>'Evento institucional','description'=>'Contenido','starts_at'=>'2026-08-01','ends_at'=>'2026-08-02','modality'=>'in_person'])->assertSessionHasNoErrors();
        $event=Event::first();
        $this->assertSame(Event::ORIGIN_STAFF,$event->origin); $this->assertSame(Event::REVIEW_NOT_REQUIRED,$event->review_status);
        $this->actingAs($admin)->patch(route('admin.events.publish',$event))->assertRedirect();
        $this->get(route('events.show',$event->slug))->assertOk();
    }

    private function researcher(): User
    {
        $u=User::factory()->create(['email_verified_at'=>now()]); $u->assignRole('INVESTIGADOR');
        $u->researcherProfile()->create(['country'=>'Ecuador','salutation'=>'Doctor','academic_title'=>'PhD','profession'=>'Investigador','research_area'=>'Otra','institution'=>'RIMIS','phone'=>'0999999999','cv_path'=>'cv/test.pdf','cv_original_name'=>'test.pdf','completed_at'=>now()]);
        return $u;
    }
    private function event(User $u): Event
    {
        return Event::create(['user_id'=>$u->id,'title'=>'Aporte propio','slug'=>'aporte-propio-'.$u->id,'description'=>'Contenido','starts_at'=>now()->addDay(),'ends_at'=>now()->addDays(2),'modality'=>'in_person','origin'=>Event::ORIGIN_RESEARCHER,'review_status'=>Event::REVIEW_DRAFT,'status'=>Event::STATUS_DRAFT]);
    }
}
