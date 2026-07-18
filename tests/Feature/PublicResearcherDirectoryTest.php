<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\ResearcherApplication;
use App\Models\ResearcherProfile;
use App\Models\ResearchPublication;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicResearcherDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); $this->seed(RolesAndPermissionsSeeder::class); Storage::fake('local'); }

    public function test_only_active_approved_complete_and_enabled_members_appear(): void
    {
        $visible=$this->researcher('Investigadora Visible','visible');
        $hidden=$this->researcher('Investigador Oculto','oculto',['profile_public'=>false]);
        $inactive=$this->researcher('Investigador Inactivo','inactivo'); $inactive->user->update(['is_active'=>false]);
        $pending=$this->researcher('Investigador Pendiente','pendiente'); $pending->user->researcherApplication->update(['status'=>'submitted']);
        $incomplete=$this->researcher('Investigador Incompleto','incompleto'); $incomplete->update(['completed_at'=>null]);
        $this->get(route('researchers.index'))->assertOk()->assertSee($visible->user->name)->assertDontSee($hidden->user->name)->assertDontSee($inactive->user->name)->assertDontSee($pending->user->name)->assertDontSee($incomplete->user->name);
        $this->get(route('researchers.show',$hidden->public_slug))->assertNotFound();
    }

    public function test_directory_search_filters_privacy_and_public_cv(): void
    {
        $profile=$this->researcher('María Investigadora','maria',['institution'=>'Instituto Andino','country'=>'Ecuador','research_area'=>'Ingeniería y tecnología','research_line'=>'Territorio digital','public_email'=>false,'public_phone'=>false,'public_cv'=>true]);
        Storage::disk('local')->put($profile->cv_path,'pdf');
        $this->get(route('researchers.index',['search'=>'María','country'=>'Ecuador','research_area'=>'Ingeniería y tecnología']))->assertOk()->assertSee('María Investigadora');
        $this->get(route('researchers.show',$profile->public_slug))->assertOk()->assertSee('Instituto Andino')->assertDontSee($profile->user->email)->assertDontSee($profile->phone);
        $this->get(route('researchers.cv',$profile->public_slug))->assertOk()->assertHeader('content-disposition');
    }

    public function test_profile_merges_owned_and_coauthored_publications_without_private_records(): void
    {
        $profile=$this->researcher('Autora Pública','autora'); $other=$this->researcher('Remitente','remitente');
        $owned=$this->publication($profile->user,'Trabajo propio');
        $coauthored=$this->publication($other->user,'Trabajo compartido');
        $coauthored->authors()->create(['user_id'=>$profile->user_id,'author_name'=>$profile->user->name,'author_order'=>1]);
        $owned->authors()->create(['user_id'=>$profile->user_id,'author_name'=>$profile->user->name,'author_order'=>1]);
        $private=$this->publication($profile->user,'Trabajo privado',['review_status'=>'draft','status'=>'draft','published_at'=>null]);
        $this->get(route('researchers.show',$profile->public_slug))->assertOk()->assertSee($owned->title)->assertSee($coauthored->title)->assertDontSee($private->title);
        $this->get(route('research-publications.show',$coauthored->slug))->assertOk()->assertSee(route('researchers.show',$profile->public_slug),false);
    }

    public function test_only_published_researcher_contributions_are_shown(): void
    {
        $profile=$this->researcher('Investigador de Eventos','eventos');
        $published=Event::create(['user_id'=>$profile->user_id,'title'=>'Evento público','slug'=>'evento-publico','description'=>'Contenido','starts_at'=>now(),'ends_at'=>now()->addDay(),'modality'=>'virtual','origin'=>'researcher','review_status'=>'approved','status'=>'published','published_at'=>now()]);
        Event::create(['user_id'=>$profile->user_id,'title'=>'Evento pendiente','slug'=>'evento-pendiente','description'=>'Contenido','starts_at'=>now(),'ends_at'=>now()->addDay(),'modality'=>'virtual','origin'=>'researcher','review_status'=>'submitted','status'=>'draft']);
        Event::create(['user_id'=>$profile->user_id,'title'=>'Evento institucional','slug'=>'evento-institucional','description'=>'Contenido','starts_at'=>now(),'ends_at'=>now()->addDay(),'modality'=>'virtual','origin'=>'staff','review_status'=>'not_required','status'=>'published','published_at'=>now()]);
        $this->get(route('researchers.show',$profile->public_slug))->assertSee($published->title)->assertDontSee('Evento pendiente')->assertDontSee('Evento institucional');
    }

    public function test_webmaster_can_hide_but_cannot_enable_unapproved_profile_and_sitemap_respects_visibility(): void
    {
        $profile=$this->researcher('Perfil Administrable','administrable'); $webmaster=$this->userWithRole('WEBMASTER');
        $this->get(route('seo.sitemap'))->assertSee(route('researchers.show',$profile->public_slug),false);
        $this->actingAs($webmaster)->patch(route('admin.researchers.visibility',$profile),['visible'=>0])->assertRedirect();
        $this->get(route('researchers.show',$profile->public_slug))->assertNotFound();
        $profile->user->researcherApplication->update(['status'=>'rejected']);
        $this->actingAs($webmaster)->patch(route('admin.researchers.visibility',$profile),['visible'=>1])->assertStatus(422);
        $this->get(route('seo.sitemap'))->assertDontSee(route('researchers.show',$profile->public_slug),false);
    }

    public function test_prepare_command_is_idempotent_and_only_enables_eligible_members(): void
    {
        $profile=$this->researcher('Perfil por Preparar','preparar',['public_slug'=>null,'profile_public'=>false]);
        $this->artisan('rimis:prepare-public-researcher-profiles')->assertSuccessful();
        $this->artisan('rimis:prepare-public-researcher-profiles')->assertSuccessful();
        $profile->refresh(); $this->assertTrue($profile->profile_public); $this->assertNotNull($profile->public_slug);
    }

    private function researcher(string $name,string $slug,array $profile=[]): ResearcherProfile
    {
        $user=$this->userWithRole('INVESTIGADOR',['name'=>$name]);
        $user->researcherApplication()->create(['status'=>ResearcherApplication::STATUS_APPROVED,'motivation'=>'Aprobada']);
        return $user->researcherProfile()->create($profile+['public_slug'=>$slug,'profile_public'=>true,'country'=>'Ecuador','salutation'=>'Doctora','academic_title'=>'PhD','profession'=>'Investigación','research_area'=>'Ciencias sociales y humanidades','research_line'=>'Innovación','institution'=>'RIMIS','phone'=>'0999999999','cv_path'=>'curricula/demo.pdf','cv_original_name'=>'demo.pdf','completed_at'=>now()]);
    }
    private function userWithRole(string $role,array $data=[]):User{$user=User::factory()->create($data+['is_active'=>true,'email_verified_at'=>now()]);$user->assignRole($role);return $user;}
    private function publication(User $user,string $title,array $extra=[]):ResearchPublication{return ResearchPublication::create($extra+['user_id'=>$user->id,'title'=>$title,'slug'=>str($title)->slug().'-'.uniqid(),'abstract'=>str_repeat('Resumen público. ',10),'publication_type'=>'scientific_article','research_area'=>'Ciencias','institution'=>'RIMIS','keywords'=>['ciencia','red','innovación'],'origin'=>'researcher','review_status'=>'approved','status'=>'published','published_at'=>now()]);}
}
