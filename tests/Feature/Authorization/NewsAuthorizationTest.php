<?php

namespace Tests\Feature\Authorization;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrator_and_webmaster_can_manage_news_but_researcher_cannot(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.news.create'))->assertOk();
        $this->actingAs($researcher)->get(route('admin.news.index'))->assertForbidden();
    }

    public function test_categories_tags_and_news_can_be_created(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $this->actingAs($administrator)->post(route('admin.news.categories.store'), [
            'name' => 'Investigación',
            'description' => 'Noticias científicas',
            'is_active' => true,
        ])->assertRedirect();
        $this->actingAs($administrator)->post(route('admin.news.tags.store'), ['name' => 'Ciencia'])->assertRedirect();

        $category = NewsCategory::firstOrFail();
        $tag = NewsTag::firstOrFail();
        $this->actingAs($administrator)->post(route('admin.news.store'), [
            'title' => 'Nueva investigación RIMIS',
            'slug' => '',
            'excerpt' => 'Resumen científico',
            'content' => '<p>Contenido seguro</p><script>alert(1)</script>',
            'category_id' => $category->id,
            'tags' => [$tag->id],
            'is_featured' => true,
            'seo_index' => true,
        ])->assertRedirect();

        $news = News::firstOrFail();
        $this->assertSame(News::STATUS_DRAFT, $news->status);
        $this->assertTrue($news->is_featured);
        $this->assertStringNotContainsString('<script>', $news->content);
        $this->assertTrue($news->tags->contains($tag));
    }

    public function test_news_can_be_published_featured_and_viewed_publicly(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $news = $this->draftNews($administrator);

        $this->get(route('news.show', $news->slug))->assertNotFound();
        $this->actingAs($administrator)->patch(route('admin.news.publish', $news))->assertRedirect();
        $this->actingAs($administrator)->patch(route('admin.news.feature', $news))->assertRedirect();

        $this->get(route('news.show', $news->slug))->assertOk()->assertSee($news->title);
        $this->get(route('news.index'))->assertOk()->assertSee($news->title);
        $this->assertTrue($news->fresh()->is_featured);
    }

    public function test_published_indexable_news_is_in_sitemap(): void
    {
        $news = $this->draftNews(User::factory()->create());
        $news->publish();

        $this->get(route('seo.sitemap'))->assertOk()->assertSee(route('news.show', $news->slug), false);
    }

    public function test_news_portal_shows_categories_and_category_lists_only_its_news(): void
    {
        $author = User::factory()->create();
        $research = NewsCategory::create(['name' => 'Investigación', 'slug' => 'investigacion', 'is_active' => true]);
        $events = NewsCategory::create(['name' => 'Eventos', 'slug' => 'eventos', 'is_active' => true]);
        $researchNews = $this->draftNews($author);
        $researchNews->update(['category_id' => $research->id]);
        $researchNews->publish();
        $eventNews = News::create([
            'user_id' => $author->id,
            'category_id' => $events->id,
            'title' => 'Congreso científico',
            'slug' => 'congreso-cientifico',
            'content' => 'Contenido',
            'status' => News::STATUS_DRAFT,
        ]);
        $eventNews->publish();

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Investigación')
            ->assertSee(route('news.category', $research), false);

        $this->get(route('news.category', $research))
            ->assertOk()
            ->assertSee($researchNews->title)
            ->assertDontSee($eventNews->title);

        $this->get(route('news.all'))->assertOk()->assertSee($researchNews->title)->assertSee($eventNews->title);
        $this->get('/')->assertOk()->assertSee($eventNews->title);
    }

    public function test_news_edit_displays_seo_suggestions_and_public_view_uses_them(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $category = NewsCategory::create(['name' => 'Investigación', 'slug' => 'investigacion', 'is_active' => true]);
        $tag = NewsTag::create(['name' => 'Innovación', 'slug' => 'innovacion']);
        $news = $this->draftNews($administrator);
        $news->update([
            'category_id' => $category->id,
            'excerpt' => 'Descubrimientos científicos para transformar la sociedad.',
        ]);
        $news->tags()->sync([$tag->id]);

        $this->actingAs($administrator)
            ->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSee('Aplicar sugerencias')
            ->assertSee('Noticia científica | RIMIS')
            ->assertSee('Descubrimientos científicos para transformar la sociedad.')
            ->assertSee('innovación');

        $news->publish();
        $this->get(route('news.show', $news->slug))
            ->assertOk()
            ->assertSee('<title>Noticia científica | RIMIS</title>', false)
            ->assertSee('name="description" content="Descubrimientos científicos para transformar la sociedad."', false)
            ->assertSee('rel="canonical" href="'.route('news.show', $news->slug).'"', false);
    }

    private function draftNews(User $author): News
    {
        return News::create([
            'user_id' => $author->id,
            'title' => 'Noticia científica',
            'slug' => 'noticia-cientifica',
            'content' => '<p>Contenido de noticia</p>',
            'status' => News::STATUS_DRAFT,
            'is_featured' => false,
            'seo_index' => true,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
