<?php

namespace Tests\Feature\Authorization;

use App\Mail\SmtpTestMail;
use App\Models\MailSetting;
use App\Models\User;
use App\Support\MailSettingsManager;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSettingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrator_can_manage_mail_settings_and_webmaster_can_only_view(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $webmaster = $this->userWithRole('WEBMASTER');
        $researcher = $this->userWithRole('INVESTIGADOR');

        $this->actingAs($administrator)->get(route('admin.settings.mail.edit'))->assertOk();
        $this->actingAs($webmaster)->get(route('admin.settings.mail.edit'))->assertOk();
        $this->actingAs($webmaster)->put(route('admin.settings.mail.update'), $this->validSettings())->assertForbidden();
        $this->actingAs($researcher)->get(route('admin.settings.mail.edit'))->assertForbidden();
    }

    public function test_mail_password_is_encrypted_and_blank_update_preserves_it(): void
    {
        $administrator = $this->userWithRole('ADMINISTRADOR');
        $this->actingAs($administrator)->put(route('admin.settings.mail.update'), $this->validSettings())->assertRedirect();

        $settings = MailSetting::firstOrFail();
        $this->assertSame('application-password', $settings->password);
        $this->assertNotSame('application-password', DB::table('mail_settings')->value('password'));

        $updated = $this->validSettings();
        $updated['password'] = '';
        $updated['from_name'] = 'RIMIS actualizado';
        $this->actingAs($administrator)->put(route('admin.settings.mail.update'), $updated)->assertRedirect();

        $this->assertSame('application-password', $settings->fresh()->password);
        $this->assertSame('RIMIS actualizado', $settings->fresh()->from_name);
    }

    public function test_saved_settings_are_applied_to_laravel_mail_configuration(): void
    {
        $settings = MailSetting::create($this->validSettings());

        app(MailSettingsManager::class)->apply($settings);

        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('ssl', config('mail.mailers.smtp.encryption'));
        $this->assertSame('notificaciones@example.com', config('mail.from.address'));
        $this->assertSame('RIMIS', config('mail.from.name'));
    }

    public function test_administrator_can_send_test_mail_with_saved_configuration(): void
    {
        Mail::fake();
        MailSetting::create($this->validSettings());

        $this->actingAs($this->userWithRole('ADMINISTRADOR'))
            ->post(route('admin.settings.mail.test'), ['test_email' => 'destino@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(SmtpTestMail::class, fn ($mail) => $mail->hasTo('destino@example.com'));
    }

    private function validSettings(): array
    {
        return [
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'notificaciones@example.com',
            'password' => 'application-password',
            'from_address' => 'notificaciones@example.com',
            'from_name' => 'RIMIS',
        ];
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
