@props(['menus'])

<header class="bg-[var(--rimis-charcoal)] shadow-lg">
    <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
        <a href="{{ url('/') }}" class="block shrink-0" aria-label="Inicio RIMIS">
            <img src="{{ asset('images/logo_rimis.png') }}" alt="RIMIS" class="h-14 w-auto object-contain">
        </a>

        <nav class="flex flex-wrap items-center justify-end gap-2" aria-label="Navegación principal">
            <x-public-menu-items :items="$menus->get('principal')?->rootItems ?? collect()" />

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-[var(--rimis-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--rimis-primary-dark)]">
                    <i class="fa-solid fa-gauge-high mr-1" aria-hidden="true"></i> Panel
                </a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
                    Iniciar sesión
                </a>
            @endauth
        </nav>
    </div>
</header>
