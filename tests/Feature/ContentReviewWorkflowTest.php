<?php
namespace Tests\Feature;
use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
class ContentReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(RolesAndPermissionsSeeder::class); Notification::fake(); }

    public function test_navigation_and_admin_middleware_are_separated_by_role(): void
    {
        $admin=$this->role('ADMINISTRADOR',false); $webmaster=$this->role('WEBMASTER',false); $researcher=$this->researcher(); $user=$this->role('USUARIO');
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Aportes recibidos')->assertDontSee('Mis aportes');
        $this->actingAs($webmaster)->get(route('dashboard'))->assertOk()->assertSee('Aportes recibidos')->assertDontSee('Mis aportes');
        $this->actingAs($researcher)->get(route('dashboard'))->assertOk()->assertSee('Mis aportes')->assertDontSee('Aportes recibidos');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Mis aportes')->assertDontSee('Aportes recibidos');
        $this->actingAs($admin)->get(route('admin.submissions.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.submissions.index'))->assertOk();
        $this->actingAs($researcher)->get(route('admin.submissions.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.submissions.index'))->assertForbidden();
    }

    public function test_editors_can_open_another_users_submission_but_researchers_cannot(): void
    {
        $owner=$this->researcher(); $event=$this->event($owner,'submitted');
        foreach([$this->role('ADMINISTRADOR',false),$this->role('WEBMASTER',false)] as $editor) $this->actingAs($editor)->get(route('admin.submissions.show',['event',$event->id]))->assertOk()->assertSee($owner->name);
        $this->actingAs($this->researcher())->get(route('researcher.submissions.event.edit',$event))->assertForbidden();
    }

    public function test_full_review_approve_publish_and_unpublish_flow(): void
    {
        $event=$this->event($this->researcher(),'submitted'); $editor=$this->role('WEBMASTER');
        $this->actingAs($editor)->patch(route('admin.submissions.start-review',['event',$event->id]))->assertRedirect();
        $this->assertSame('under_review',$event->fresh()->review_status); $this->assertSame($editor->id,$event->fresh()->reviewed_by);
        $this->actingAs($editor)->patch(route('admin.submissions.approve',['event',$event->id]),['review_notes'=>'Contenido aprobado'])->assertRedirect();
        $event->refresh(); $this->assertSame('approved',$event->review_status); $this->assertSame('draft',$event->status); $this->assertNull($event->published_at);
        $this->actingAs($editor)->patch(route('admin.submissions.publish',['event',$event->id]))->assertRedirect();
        $this->get(route('events.show',$event->slug))->assertOk();
        $this->actingAs($editor)->patch(route('admin.submissions.unpublish',['event',$event->id]))->assertRedirect();
        $event->refresh(); $this->assertSame('approved',$event->review_status); $this->assertSame('draft',$event->status); $this->get(route('events.show',$event->slug))->assertNotFound();
        $this->assertDatabaseCount('content_review_history',4);
    }

    public function test_observation_requires_notes_and_researcher_can_resubmit(): void
    {
        $owner=$this->researcher(); $event=$this->event($owner,'submitted'); $editor=$this->role('ADMINISTRADOR');
        $this->actingAs($editor)->patch(route('admin.submissions.start-review',['event',$event->id]));
        $this->actingAs($editor)->patch(route('admin.submissions.observe',['event',$event->id]),['review_notes'=>'corto'])->assertSessionHasErrors('review_notes');
        $this->actingAs($editor)->patch(route('admin.submissions.observe',['event',$event->id]),['review_notes'=>'Amplíe la información del evento.'])->assertRedirect();
        $this->assertTrue($event->fresh()->isEditableByResearcher());
        $this->actingAs($owner)->post(route('researcher.submissions.event.submit',$event))->assertRedirect();
        $event->refresh();
        $this->assertSame('submitted',$event->review_status);
        $this->assertNull($event->review_started_at);
        $this->assertNull($event->reviewed_at);
        $this->assertNull($event->reviewed_by);
        $this->assertNull($event->review_notes);
    }

    public function test_all_content_types_can_enter_review_and_invalid_transitions_conflict(): void
    {
        $owner=$this->researcher(); $editor=$this->role('WEBMASTER');
        $models=[['event',$this->event($owner,'submitted')],['bulletin',Bulletin::create(['user_id'=>$owner->id,'title'=>'Boletín','slug'=>'boletin-editorial','pdf_path'=>'x.pdf','pdf_original_name'=>'x.pdf','origin'=>'researcher','review_status'=>'submitted','status'=>'draft','submitted_at'=>now()])],['call',CallForProposal::create(['user_id'=>$owner->id,'title'=>'Convocatoria','slug'=>'convocatoria-editorial','description'=>'Texto','opens_at'=>now(),'closes_at'=>now()->addDay(),'bases_pdf_path'=>'x.pdf','bases_pdf_original_name'=>'x.pdf','origin'=>'researcher','review_status'=>'submitted','status'=>'draft','submitted_at'=>now()])]];
        foreach($models as [$type,$model]) { $this->actingAs($editor)->patch(route('admin.submissions.approve',[$type,$model->id]))->assertStatus(409); $this->actingAs($editor)->patch(route('admin.submissions.start-review',[$type,$model->id]))->assertRedirect(); $this->assertSame('under_review',$model->fresh()->review_status); $this->actingAs($editor)->patch(route('admin.submissions.start-review',[$type,$model->id]))->assertStatus(409); }
    }

    public function test_editorial_edit_preserves_ownership_and_review_and_generates_history(): void
    {
        $owner=$this->researcher(); $editor=$this->role('WEBMASTER'); $event=$this->event($owner,'approved');
        $event->update(['reviewed_by'=>$editor->id,'reviewed_at'=>now()]);
        $this->actingAs($editor)->put(route('admin.events.update',$event),[
            'title'=>'Título corregido','description'=>'Contenido editorial corregido','starts_at'=>now()->addDays(3)->format('Y-m-d H:i:s'),'ends_at'=>now()->addDays(4)->format('Y-m-d H:i:s'),'modality'=>'virtual',
            'user_id'=>$editor->id,'origin'=>'staff','review_status'=>'rejected','reviewed_by'=>$owner->id,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $event->refresh();
        $this->assertSame($owner->id,$event->user_id);
        $this->assertSame('researcher',$event->origin);
        $this->assertSame('approved',$event->review_status);
        $this->assertSame($editor->id,$event->reviewed_by);
        $this->assertDatabaseHas('content_review_history',['reviewable_type'=>Event::class,'reviewable_id'=>$event->id,'new_status'=>'editorial:content']);
    }

    private function role(string $role,bool $verified=true): User { $u=User::factory()->create(['email_verified_at'=>$verified?now():null]); $u->assignRole($role); return $u; }
    private function researcher(): User { $u=$this->role('INVESTIGADOR'); $u->researcherProfile()->create(['country'=>'Ecuador','salutation'=>'Doctor','academic_title'=>'PhD','profession'=>'Investigador','research_area'=>'Otra','institution'=>'RIMIS','phone'=>'0999999999','cv_path'=>'cv.pdf','cv_original_name'=>'cv.pdf','completed_at'=>now()]); return $u; }
    private function event(User $u,string $review): Event { return Event::create(['user_id'=>$u->id,'title'=>'Evento aportado','slug'=>'evento-'.$u->id.'-'.$review,'description'=>'Contenido','starts_at'=>now()->addDay(),'ends_at'=>now()->addDays(2),'modality'=>'in_person','origin'=>'researcher','review_status'=>$review,'status'=>'draft','submitted_at'=>now()]); }
}
