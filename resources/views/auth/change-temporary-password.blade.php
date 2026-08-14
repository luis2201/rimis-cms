<x-guest-layout>
    <div x-data="{ showPassword: false }">
        <header class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[rgba(31,157,173,.12)] text-2xl text-[var(--rimis-primary-dark)]">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <p class="mt-5 text-[.68rem] font-extrabold uppercase tracking-[.2em] text-[var(--rimis-coral)]">Primer inicio de sesión</p>
            <h1 class="mt-2 font-serif text-2xl font-bold tracking-tight text-slate-800">Crea tu contraseña personal</h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">Por seguridad, reemplaza la contraseña temporal antes de ingresar a la plataforma.</p>
        </header>

        <div class="mt-6 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm text-slate-600">
            <div class="flex gap-3">
                <i class="fa-solid fa-circle-info mt-0.5 text-[var(--rimis-primary)]"></i>
                <p>Utiliza al menos 8 caracteres. Recomendamos combinar mayúsculas, minúsculas, números y símbolos.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('password.force-update') }}" class="mt-7 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="password" class="block text-sm font-bold text-slate-700">Nueva contraseña</label>
                <div class="relative mt-2">
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autofocus autocomplete="new-password" placeholder="Ingresa tu nueva contraseña" class="block w-full rounded-lg border-slate-300 py-3 pl-4 pr-12 text-sm shadow-sm transition focus:border-[var(--rimis-primary)] focus:ring-[var(--rimis-primary)] @error('password') border-[var(--rimis-coral)] @enderror">
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-slate-400 transition hover:text-[var(--rimis-primary-dark)]" :aria-label="showPassword ? 'Ocultar contraseñas' : 'Mostrar contraseñas'">
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('password')<p class="mt-2 flex items-center gap-2 text-xs font-semibold text-[var(--rimis-coral)]"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-slate-700">Confirmar contraseña</label>
                <div class="relative mt-2">
                    <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu nueva contraseña" class="block w-full rounded-lg border-slate-300 py-3 pl-4 pr-12 text-sm shadow-sm transition focus:border-[var(--rimis-primary)] focus:ring-[var(--rimis-primary)]">
                    <span class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-300"><i class="fa-solid fa-lock"></i></span>
                </div>
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[var(--rimis-primary)] px-5 py-3.5 text-sm font-extrabold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[var(--rimis-primary-dark)] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--rimis-primary)] focus:ring-offset-2">
                Guardar y continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <p class="mt-6 flex items-center justify-center gap-2 text-center text-xs text-slate-400"><i class="fa-solid fa-lock"></i>Tu contraseña se almacena de forma segura.</p>
    </div>
</x-guest-layout>
