<?php

namespace App\Support;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MailSettingsManager
{
    public function apply(?MailSetting $settings = null): bool
    {
        if (! Schema::hasTable('mail_settings')) {
            return false;
        }

        $settings ??= MailSetting::first();

        if (! $settings?->enabled) {
            return false;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => $settings->port,
            'mail.mailers.smtp.encryption' => $settings->encryption ?: null,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name,
        ]);

        try {
            app('mail.manager')->purge('smtp');
        } catch (Throwable) {
            // The mail manager may not be resolved yet or may be replaced by a test fake.
        }

        return true;
    }
}
