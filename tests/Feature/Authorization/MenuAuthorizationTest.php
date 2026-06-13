<?php

namespace Tests\Feature\Authorization;

use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrator_and_webmaster_can_manage_menus_but_researcher_cannot(): void
    {
        $menu = Menu::create(['name' => 'Principal', 'location' => 'principal', 'is_active' => true]);
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.menus.index'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.menus.show', $menu))->assertOk();
        $this->actingAs($researcher)->get(route('admin.menus.index'))->assertForbidden();
    }

    public function test_menu_can_be_created_updated_and_deleted(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.menus.store'), [
            'name' => 'Footer institucional',
            'location' => 'footer',
            'description' => 'Enlaces de pie de página',
            'is_active' => true,
        ])->assertRedirect();

        $menu = Menu::where('location', 'footer')->firstOrFail();

        $this->actingAs($administrator)->put(route('admin.menus.update', $menu), [
            'name' => 'Footer',
            'location' => 'footer',
            'description' => '',
            'is_active' => false,
        ])->assertRedirect(route('admin.menus.index'));

        $this->assertFalse($menu->fresh()->is_active);

        $this->actingAs($administrator)->delete(route('admin.menus.destroy', $menu))->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_items_support_submenus_status_and_ordering(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $menu = Menu::create(['name' => 'Principal', 'location' => 'principal', 'is_active' => true]);

        foreach (['Inicio', 'Investigación'] as $label) {
            $this->actingAs($administrator)->post(route('admin.menus.items.store', $menu), [
                'label' => $label,
                'url' => '/'.strtolower($label),
                'target' => '_self',
                'is_active' => true,
            ])->assertRedirect();
        }

        $inicio = $menu->items()->where('label', 'Inicio')->firstOrFail();
        $investigacion = $menu->items()->where('label', 'Investigación')->firstOrFail();

        $this->actingAs($administrator)->post(route('admin.menus.items.store', $menu), [
            'label' => 'Proyectos',
            'url' => '/proyectos',
            'target' => '_self',
            'parent_id' => $investigacion->id,
            'is_active' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'label' => 'Proyectos',
            'parent_id' => $investigacion->id,
            'is_active' => false,
        ]);

        $this->actingAs($administrator)->patch(route('admin.menus.items.move', [$menu, $investigacion, 'up']))->assertRedirect();
        $this->assertLessThan($inicio->fresh()->sort_order, $investigacion->fresh()->sort_order);
    }

    public function test_active_menu_items_are_displayed_on_public_page(): void
    {
        $menu = Menu::create(['name' => 'Principal', 'location' => 'principal', 'is_active' => true]);
        $menu->items()->create(['label' => 'Noticias RIMIS', 'url' => '/noticias', 'icon' => 'fa-solid fa-newspaper', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);
        $menu->items()->create(['label' => 'Oculto RIMIS', 'url' => '/oculto', 'target' => '_self', 'sort_order' => 2, 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Noticias RIMIS')
            ->assertSee('fa-solid fa-newspaper')
            ->assertDontSee('Oculto RIMIS');
    }

    public function test_principal_menu_is_displayed_across_public_sections(): void
    {
        $menu = Menu::create(['name' => 'Principal', 'location' => 'principal', 'is_active' => true]);
        $menu->items()->create(['label' => 'Enlace global RIMIS', 'url' => '/enlace-global', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);
        $page = Page::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Página pública',
            'slug' => 'pagina-publica',
            'content' => 'Contenido',
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        foreach (['/', route('pages.show', $page->slug), route('news.index'), route('bulletins.index')] as $url) {
            $this->get($url)->assertOk()->assertSee('Enlace global RIMIS');
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
