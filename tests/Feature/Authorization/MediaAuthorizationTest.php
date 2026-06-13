<?php

namespace Tests\Feature\Authorization;

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MediaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['media.view', 'media.create', 'media.edit', 'media.delete'] as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        Storage::fake('public');
    }

    public function test_user_without_media_permissions_cannot_access_media_routes(): void
    {
        $user = User::factory()->create();
        $media = $this->createMedia($user);

        $this->actingAs($user)->get(route('admin.media-files.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media-files.show', $media))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media-files.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media-files.edit', $media))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.media-files.destroy', $media))->assertForbidden();
    }

    public function test_media_view_permission_only_allows_read_routes(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('media.view');
        $media = $this->createMedia($user);

        $this->actingAs($user)->get(route('admin.media-files.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.media-files.show', $media))->assertOk();
        $this->actingAs($user)->get(route('admin.media-files.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media-files.edit', $media))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.media-files.destroy', $media))->assertForbidden();
    }

    public function test_media_action_permissions_allow_their_routes(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['media.create', 'media.edit', 'media.delete']);
        $media = $this->createMedia($user);

        $this->actingAs($user)->get(route('admin.media-files.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.media-files.edit', $media))->assertOk();
        $this->actingAs($user)->delete(route('admin.media-files.destroy', $media))
            ->assertRedirect(route('admin.media-files.index'));
    }

    public function test_editor_image_library_requires_view_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('admin.media-files.editor.images'))->assertForbidden();

        $user->givePermissionTo('media.view');
        $this->createImage($user);

        $this->actingAs($user)
            ->getJson(route('admin.media-files.editor.images'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'example.jpg',
                'url' => '/storage/media/example.jpg',
            ]);
    }

    public function test_editor_can_upload_images_with_create_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('media.create');

        $response = $this->actingAs($user)
            ->postJson(route('admin.media-files.editor.upload'), [
                'file' => UploadedFile::fake()->image('editor-image.jpg'),
            ])
            ->assertOk()
            ->assertJsonStructure(['location', 'id']);

        $this->assertDatabaseHas('media_files', ['original_name' => 'editor-image.jpg', 'file_type' => 'image']);
        $this->assertStringStartsWith('/storage/media/', $response->json('location'));
        $this->assertStringNotContainsString('localhost', $response->json('location'));
    }

    private function createMedia(User $user): MediaFile
    {
        return MediaFile::create([
            'user_id' => $user->id,
            'name' => 'test-file',
            'original_name' => 'test-file.pdf',
            'file_path' => 'media/test-file.pdf',
            'disk' => 'public',
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => true,
        ]);
    }

    private function createImage(User $user): MediaFile
    {
        return MediaFile::create([
            'user_id' => $user->id,
            'name' => 'example',
            'original_name' => 'example.jpg',
            'file_path' => 'media/example.jpg',
            'disk' => 'public',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'status' => true,
        ]);
    }
}
