<?php

namespace Tests\Feature\Authorization;

use App\Models\Bulletin;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulletinAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrator_and_webmaster_can_manage_bulletins_but_researcher_cannot(): void
    {
        $this->actingAs($this->userWithRole('ADMINISTRADOR'))->get(route('admin.bulletins.index'))->assertOk();
        $this->actingAs($this->userWithRole('WEBMASTER'))->get(route('admin.bulletins.create'))->assertOk();
        $this->actingAs($this->userWithRole('INVESTIGADOR'))->get(route('admin.bulletins.index'))->assertForbidden();
    }

    public function test_bulletin_requires_pdf_and_starts_as_draft(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($administrator)->post(route('admin.bulletins.store'), [
            'title' => 'Boletín científico',
            'description' => 'Edición de junio',
            'pdf' => UploadedFile::fake()->create('boletin.pdf', 400, 'application/pdf'),
        ])->assertRedirect();

        $bulletin = Bulletin::firstOrFail();
        $this->assertSame(Bulletin::STATUS_DRAFT, $bulletin->status);
        $this->assertSame('boletin.pdf', $bulletin->pdf_original_name);
        Storage::disk('local')->assertExists($bulletin->pdf_path);

        $this->actingAs($administrator)->post(route('admin.bulletins.store'), [
            'title' => 'Archivo inválido',
            'pdf' => UploadedFile::fake()->create('archivo.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('pdf');
    }

    public function test_published_bulletin_is_public_downloadable_and_in_sitemap(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $bulletin = $this->draftBulletin($administrator);

        $this->get(route('bulletins.show', $bulletin->slug))->assertNotFound();
        $this->get(route('bulletins.download', $bulletin))->assertNotFound();

        $this->actingAs($administrator)->patch(route('admin.bulletins.publish', $bulletin))->assertRedirect();

        $this->get(route('bulletins.index'))->assertOk()->assertSee($bulletin->title);
        $this->get(route('bulletins.show', $bulletin->slug))->assertOk()->assertSee($bulletin->pdf_original_name);
        $this->get(route('bulletins.download', $bulletin))->assertOk();
        $this->get(route('seo.sitemap'))->assertOk()->assertSee(route('bulletins.show', $bulletin->slug), false);
    }

    public function test_pdf_can_be_replaced_and_is_deleted_with_bulletin(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $bulletin = $this->draftBulletin($administrator);
        $oldPath = $bulletin->pdf_path;

        $this->actingAs($administrator)->put(route('admin.bulletins.update', $bulletin), [
            'title' => $bulletin->title,
            'slug' => $bulletin->slug,
            'pdf' => UploadedFile::fake()->create('actualizado.pdf', 500, 'application/pdf'),
        ])->assertRedirect();

        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($bulletin->fresh()->pdf_path);
        $newPath = $bulletin->fresh()->pdf_path;

        $this->actingAs($administrator)->delete(route('admin.bulletins.destroy', $bulletin))->assertRedirect();
        Storage::disk('local')->assertMissing($newPath);
    }

    private function draftBulletin(User $author): Bulletin
    {
        $file = UploadedFile::fake()->create('boletin.pdf', 400, 'application/pdf');
        $path = $file->store('bulletins', 'local');

        return Bulletin::create([
            'user_id' => $author->id,
            'title' => 'Boletín mensual',
            'slug' => 'boletin-mensual',
            'description' => 'Publicación mensual',
            'pdf_path' => $path,
            'pdf_original_name' => 'boletin.pdf',
            'pdf_size' => $file->getSize(),
            'status' => Bulletin::STATUS_DRAFT,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
