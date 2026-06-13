<?php

namespace App\Http\Controllers;

use App\Mail\SmtpTestMail;
use App\Models\MailSetting;
use App\Support\MailSettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.mail', ['settings' => MailSetting::first()]);
    }

    public function update(Request $request, MailSettingsManager $manager): RedirectResponse
    {
        $settings = MailSetting::first();
        $request->merge(['enabled' => $request->boolean('enabled')]);
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', Rule::in(['ssl', 'tls'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$settings ? 'nullable' : 'required', 'string', 'max:1000'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        if ($settings && blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $settings = MailSetting::updateOrCreate(['id' => $settings?->id ?? 1], $validated);
        $manager->apply($settings);

        return back()->with('success', 'Configuración de correo actualizada correctamente.');
    }

    public function sendTest(Request $request, MailSettingsManager $manager): RedirectResponse
    {
        $validated = $request->validate(['test_email' => ['required', 'email', 'max:255']]);
        $settings = MailSetting::first();

        if (! $settings?->enabled) {
            return back()->with('error', 'Activa y guarda la configuración SMTP antes de enviar una prueba.');
        }

        try {
            $manager->apply($settings);
            Mail::to($validated['test_email'])->send(new SmtpTestMail());
        } catch (Throwable $exception) {
            Log::error('SMTP test failed.', ['exception' => $exception]);

            return back()->with('error', 'No se pudo enviar el correo de prueba. Revisa el servidor, puerto, cifrado y credenciales.');
        }

        return back()->with('success', 'Correo de prueba enviado a '.$validated['test_email'].'.');
    }
}
