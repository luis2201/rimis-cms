<?php

namespace Tests\Feature\Authorization;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrator_and_webmaster_can_manage_pages_but_researcher_cannot(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.pages.create'))->assertOk();
        $this->actingAs($researcher)->get(route('admin.pages.index'))->assertForbidden();
    }

    public function test_created_page_starts_as_draft_and_can_be_edited(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.pages.store'), [
            'title' => 'Acerca de RIMIS',
            'slug' => '',
            'excerpt' => 'Nuestra organización',
            'content' => 'Contenido inicial',
            'show_title' => true,
        ])->assertRedirect();

        $page = Page::where('slug', 'acerca-de-rimis')->firstOrFail();
        $this->assertSame(Page::STATUS_DRAFT, $page->status);
        $this->assertNull($page->published_at);

        $this->actingAs($administrator)->put(route('admin.pages.update', $page), [
            'title' => 'Quiénes somos',
            'slug' => 'quienes-somos',
            'excerpt' => '',
            'content' => 'Contenido actualizado',
            'show_title' => true,
        ])->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'quienes-somos', 'content' => 'Contenido actualizado']);
    }

    public function test_page_can_be_created_without_introductory_content_for_block_builder(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.pages.store'), [
            'title' => 'Página por bloques',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'show_title' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('pages', ['slug' => 'pagina-por-bloques', 'content' => '']);
    }

    public function test_only_published_pages_are_public_and_can_be_unpublished(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);

        $this->get(route('pages.show', $page->slug))->assertNotFound();

        $this->actingAs($administrator)->patch(route('admin.pages.publish', $page))->assertRedirect();
        $this->get(route('pages.show', $page->slug))->assertOk()->assertSee($page->title);

        $this->actingAs($administrator)->patch(route('admin.pages.unpublish', $page))->assertRedirect();
        $this->get(route('pages.show', $page->slug))->assertNotFound();
    }

    public function test_page_can_be_deleted(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);

        $this->actingAs($administrator)->delete(route('admin.pages.destroy', $page))->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_page_content_keeps_safe_formatting_and_removes_unsafe_html(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.pages.store'), [
            'title' => 'Página segura',
            'slug' => 'pagina-segura',
            'excerpt' => '',
            'content' => '<h2 style="text-align: center; color: red; position: fixed;">Formato permitido</h2><script>alert("xss")</script><img src="/storage/media/example.jpg" alt="Ejemplo" style="width: 50%; float: right; position: fixed;">',
            'show_title' => true,
        ])->assertRedirect();

        $page = Page::where('slug', 'pagina-segura')->firstOrFail();
        $this->assertStringContainsString('<h2', $page->content);
        $this->assertStringContainsString('Formato permitido</h2>', $page->content);
        $this->assertStringContainsString('<img', $page->content);
        $this->assertStringContainsString('text-align:center', $page->content);
        $this->assertStringContainsString('width:50%', $page->content);
        $this->assertStringNotContainsString('position:', $page->content);
        $this->assertStringNotContainsString('<script>', $page->content);

        $page->publish();
        $this->get(route('pages.show', $page->slug))->assertOk()->assertSee('text-align:center', false)->assertDontSee('<script>', false);
    }

    public function test_page_blocks_can_be_created_ordered_updated_and_deleted(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);

        $this->actingAs($administrator)
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('Constructor de bloques');

        $this->actingAs($administrator)->post(route('admin.pages.blocks.store', $page), [
            'type' => 'hero',
            'name' => 'Portada principal',
            'title' => 'Investigación que transforma',
            'subtitle' => 'Conecta con RIMIS',
            'is_active' => true,
        ])->assertRedirect();

        $hero = $page->blocks()->firstOrFail();
        $this->assertSame('Investigación que transforma', $hero->data['title']);

        $this->actingAs($administrator)->post(route('admin.pages.blocks.store', $page), [
            'type' => 'text',
            'content' => '<p>Contenido seguro</p><script>alert(1)</script>',
            'is_active' => true,
        ])->assertRedirect();

        $text = $page->blocks()->where('type', 'text')->firstOrFail();
        $this->assertStringNotContainsString('<script>', $text->data['content']);

        $this->actingAs($administrator)->patch(route('admin.pages.blocks.move', [$page, $text, 'up']))->assertRedirect();
        $this->assertLessThan($hero->fresh()->sort_order, $text->fresh()->sort_order);

        $this->actingAs($administrator)->put(route('admin.pages.blocks.update', [$page, $hero]), [
            'type' => 'hero',
            'title' => 'Nuevo título',
            'is_active' => false,
        ])->assertRedirect();
        $this->assertFalse($hero->fresh()->is_active);

        $this->actingAs($administrator)->delete(route('admin.pages.blocks.destroy', [$page, $text]))->assertRedirect();
        $this->assertDatabaseMissing('page_blocks', ['id' => $text->id]);
    }

    public function test_page_title_can_be_hidden_from_public_view(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);
        $page->update(['show_title' => false, 'content' => 'Contenido visible']);
        $page->publish();

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertDontSee('<h1', false)
            ->assertSee('Contenido visible')
            ->assertSee("<title>{$page->title} | RIMIS</title>", false);
    }

    public function test_page_edit_displays_local_seo_suggestions_from_content(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);

        $this->actingAs($administrator)
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('SEO individual')
            ->assertSee('Aplicar sugerencias')
            ->assertSee('Página institucional | RIMIS');
    }

    public function test_active_page_blocks_are_rendered_publicly(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $page = $this->draftPage($administrator);
        $page->blocks()->create([
            'type' => 'faq',
            'data' => ['title' => 'Preguntas frecuentes', 'items' => [['¿Cómo participar?', 'Crea una cuenta.']]],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $page->blocks()->create([
            'type' => 'text',
            'data' => ['content' => '<p>Bloque oculto</p>'],
            'sort_order' => 2,
            'is_active' => false,
        ]);
        $page->publish();

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Preguntas frecuentes')
            ->assertSee('¿Cómo participar?')
            ->assertDontSee('Bloque oculto');
    }

    public function test_dynamic_list_block_can_render_recent_news_from_a_category(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $category = NewsCategory::create(['name' => 'Ciencia', 'slug' => 'ciencia', 'is_active' => true]);
        $news = News::create([
            'user_id' => $administrator->id,
            'category_id' => $category->id,
            'title' => 'Hallazgo reciente',
            'slug' => 'hallazgo-reciente',
            'content' => 'Contenido',
            'status' => News::STATUS_DRAFT,
        ]);
        $news->publish();
        $page = $this->draftPage($administrator);
        $page->publish();

        $this->actingAs($administrator)->post(route('admin.pages.blocks.store', $page), [
            'type' => 'dynamic_list',
            'title' => 'Últimas investigaciones',
            'source' => 'news_category',
            'category_id' => $category->id,
            'limit' => 3,
            'is_active' => true,
        ])->assertRedirect();

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Últimas investigaciones')
            ->assertSee($news->title)
            ->assertSee(route('news.show', $news->slug), false);
    }

    private function draftPage(User $author): Page
    {
        return Page::create([
            'user_id' => $author->id,
            'title' => 'Página institucional',
            'slug' => 'pagina-institucional',
            'content' => 'Contenido',
            'status' => Page::STATUS_DRAFT,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
