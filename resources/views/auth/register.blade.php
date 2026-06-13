<x-guest-layout>
    <div class="mb-7 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--rimis-primary)]">Únete a RIMIS</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Crear una cuenta</h1>
        <p class="mt-2 text-sm text-slate-500">Regístrate para formar parte de nuestra red de investigación.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input id="name" class="mt-2 block w-full px-3 py-2.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Tu nombre completo" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-2 block w-full px-3 py-2.5" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nombre@ejemplo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="mt-2 block w-full px-3 py-2.5" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full px-3 py-2.5" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full rounded-lg bg-[var(--rimis-primary)] px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[var(--rimis-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--rimis-primary)] focus:ring-offset-2">
                Crear cuenta
            </button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="font-bold text-[var(--rimis-primary-dark)] hover:underline">Inicia sesión</a>
    </p>
</x-guest-layout>
