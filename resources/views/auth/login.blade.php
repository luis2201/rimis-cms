<x-guest-layout>
    <div class="mb-7 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--rimis-primary)]">Bienvenido a RIMIS</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Iniciar sesión</h1>
        <p class="mt-2 text-sm text-slate-500">Accede al panel de gestión de la plataforma.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-2 block w-full px-3 py-2.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nombre@ejemplo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="mt-2 block w-full px-3 py-2.5" type="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[var(--rimis-primary)] shadow-sm focus:ring-[var(--rimis-primary)]" name="remember">
                <span class="ml-2 text-sm text-slate-600">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-semibold text-[var(--rimis-primary-dark)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--rimis-primary)] focus:ring-offset-2" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <div>
            <button type="submit" class="w-full rounded-lg bg-[var(--rimis-primary)] px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[var(--rimis-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--rimis-primary)] focus:ring-offset-2">
                Ingresar
            </button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        ¿Aún no tienes una cuenta?
        <a href="{{ route('subscriptions.index') }}" class="font-bold text-[var(--rimis-primary-dark)] hover:underline">Solicita una suscripción</a>
    </p>
</x-guest-layout>
