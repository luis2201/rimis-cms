<?php
namespace Tests\Feature;
use App\Models\{Subscription,User};
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class SubscriptionWorkflowTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp():void{parent::setUp();$this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);Notification::fake();Storage::fake('public');}
    public function test_professional_subscription_is_public_and_does_not_create_user():void
    {
        $this->post(route('subscriptions.store','professional'),$this->professional())->assertRedirect(route('subscriptions.sent'));
        $s=Subscription::firstOrFail();$this->assertSame('submitted',$s->status);$this->assertNull($s->user_id);$this->assertDatabaseMissing('users',['email'=>$s->email]);
        $this->assertNotNull($s->personal_photo_path);Storage::disk('public')->assertExists($s->personal_photo_path);
        Notification::assertSentOnDemand(SubscriptionStatusNotification::class);
    }
    public function test_conditional_fields_are_focusable_only_when_their_option_is_selected():void
    {
        $this->get(route('subscriptions.create','professional'))
            ->assertOk()
            ->assertSee('id="first_names" type="text"', false)
            ->assertDontSee('type="professional"', false)
            ->assertSee('x-bind:required="otherArea"', false);

        $this->get(route('subscriptions.create','institutional'))
            ->assertOk()
            ->assertSee('x-bind:required="otherInstitution"', false);
    }
    public function test_duplicate_email_identification_and_phone_are_rejected():void
    {
        $this->post(route('subscriptions.store','professional'),$this->professional());
        $this->post(route('subscriptions.store','professional'),$this->professional())->assertSessionHasErrors(['email','national_id','contact_phone']);
    }
    public function test_approval_creates_verified_member_with_temporary_password():void
    {
        $admin=User::factory()->create();$admin->assignRole('ADMINISTRADOR');$this->post(route('subscriptions.store','professional'),$this->professional());$s=Subscription::first();
        $this->actingAs($admin)->patch(route('admin.subscriptions.review',$s))->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.subscriptions.approve',$s))->assertRedirect();
        $s->refresh();$user=$s->user;$this->assertSame('approved',$s->status);$this->assertTrue($user->hasRole('INVESTIGADOR'));$this->assertTrue($user->hasVerifiedEmail());$this->assertTrue($user->must_change_password);
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.change'));
        $this->actingAs($user)->put(route('password.force-update'),['password'=>'NuevaClave.2026','password_confirmation'=>'NuevaClave.2026'])->assertRedirect(route('dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
    }
    public function test_institution_gets_same_publishing_permissions():void
    {
        $admin=User::factory()->create();$admin->assignRole('ADMINISTRADOR');$this->post(route('subscriptions.store','institutional'),$this->institutional());$s=Subscription::first();$this->actingAs($admin)->patch(route('admin.subscriptions.review',$s));$this->actingAs($admin)->patch(route('admin.subscriptions.approve',$s));$user=$s->fresh()->user;$user->forceFill(['must_change_password'=>false])->save();
        $this->assertSame('Autoridad Demo',$s->principal_authority_name);$this->assertSame('María Solicitante',$s->requester_name);$this->assertNotNull($s->institution_logo_path);Storage::disk('public')->assertExists($s->institution_logo_path);
        $this->assertTrue($user->hasRole('INSTITUCIONAL'));$this->assertTrue($user->can('events.submit'));$this->assertTrue($user->can('research-publications.create'));$this->actingAs($user)->get(route('researcher.publications.create'))->assertOk();
    }
    private function professional():array{return ['first_names'=>'Ana María','last_names'=>'Pérez López','email'=>'ana@rimis.test','national_id'=>'1300000001','orcid'=>'0000-0000-0000-000X','undergraduate_title'=>'Ingeniera','research_areas'=>['Ingeniería y tecnología','Otra'],'other_research_area'=>'Robótica','teaching_functions'=>'Docencia universitaria en sistemas.','current_research_functions'=>'Dirección de proyectos tecnológicos.','research_activity'=>'Investigación aplicada en sistemas autónomos.','personal_photo'=>UploadedFile::fake()->image('foto.jpg',400,400),'country'=>'Ecuador','city'=>'Portoviejo','contact_phone'=>'0990000001'];}
    private function institutional():array{return ['institution_name'=>'Instituto Demo','principal_authority_name'=>'Autoridad Demo','foundation_year'=>2005,'institution_logo'=>UploadedFile::fake()->image('logo.png',400,400),'institution_type'=>'Pública','requester_name'=>'María Solicitante','requester_position'=>'Directora de investigación','requester_email'=>'solicitante@rimis.test','country'=>'Ecuador','city'=>'Portoviejo','email'=>'instituto@rimis.test','main_phone'=>'052000001','mobile_phone'=>'0990000002'];}
}
