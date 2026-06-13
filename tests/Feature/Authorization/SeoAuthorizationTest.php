<?php

namespace Tests\Feature\Authorization;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        SiteSetting::create([
            'site_name' => 'RIMIS',
            'meta_title' => 'SEO global RIMIS',
            'meta_description' => 'Descripción global del sitio RIMIS.',
            'seo_index' => true,
        ]);
    }

    public function test_administrator_and_webmaster_can_manage_global_seo_but_researcher_cannot(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.seo.edit'))->assertOk();
        $this->actingAs($webmaster)->put(route('admin.seo.update'), [
            'meta_title' => 'Nueva configuración SEO',
            'meta_description' => 'Descripción SEO actualizada.',
            'meta_keywords' => 'ciencia, investigación',
            'og_image' => '',
            'seo_index' => true,
            'twitter_card' => 'summary_large_image',
        ])->assertRedirect();
        $this->actingAs($researcher)->get(route('admin.seo.edit'))->assertForbidden();

        $this->assertDatabaseHas('site_settings', ['meta_title' => 'Nueva configuración SEO']);
    }

    public function test_global_seo_displays_suggestions_from_site_information(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        SiteSetting::findOrFail(1)->update([
            'site_description' => 'Red académica multidisciplinaria',
            'site_slogan' => 'Impulsando la investigación científica',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('Aplicar sugerencias')
            ->assertSee('RIMIS | Impulsando la investigación científica')
            ->assertSee('Red académica multidisciplinaria Impulsando la investigación científica');
    }

    public function test_public_page_uses_individual_seo_and_renders_social_metadata(): void
    {
        $page = $this->publishedPage([
            'seo_title' => 'Título SEO individual',
            'seo_description' => 'Descripción SEO individual.',
            'seo_keywords' => 'rimis, ciencia',
            'seo_canonical_url' => 'https://example.com/canonica',
            'seo_index' => true,
        ]);

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('<title>Título SEO individual</title>', false)
            ->assertSee('name="description" content="Descripción SEO individual."', false)
            ->assertSee('property="og:title" content="Título SEO individual"', false)
            ->assertSee('rel="canonical" href="https://example.com/canonica"', false)
            ->assertSee('name="robots" content="index, follow"', false);
    }

    public function test_sitemap_excludes_pages_marked_as_noindex_and_robots_uses_global_setting(): void
    {
        $indexed = $this->publishedPage(['title' => 'Página indexada', 'slug' => 'pagina-indexada', 'seo_index' => true]);
        $hidden = $this->publishedPage(['title' => 'Página oculta', 'slug' => 'pagina-oculta', 'seo_index' => false]);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertSee(route('pages.show', $indexed->slug), false)
            ->assertDontSee(route('pages.show', $hidden->slug), false);

        $this->get(route('seo.robots'))->assertOk()->assertSee('Allow: /')->assertSee(route('seo.sitemap'));
    }

    private function publishedPage(array $attributes = []): Page
    {
        return Page::create(array_merge([
            'title' => 'Página institucional',
            'slug' => 'pagina-institucional',
            'content' => 'Contenido científico institucional.',
            'show_title' => true,
            'seo_index' => true,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ], $attributes));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
