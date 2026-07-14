<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyResearcherEmail;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('USUARIO');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyResearcherEmail::class);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_researcher_receives_new_verification_link_after_changing_email(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $researcher = User::factory()->create();
        $researcher->assignRole('INVESTIGADOR');

        $this->actingAs($researcher)
            ->patch(route('profile.update'), [
                'name' => $researcher->name,
                'email' => 'correo-corregido@example.com',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertFalse($researcher->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($researcher, VerifyResearcherEmail::class);
    }

    public function test_user_can_deactivate_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertFalse($user->fresh()->is_active);
        $this->assertNotNull($user->fresh()->deactivated_at);
    }

    public function test_correct_password_must_be_provided_to_deactivate_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
