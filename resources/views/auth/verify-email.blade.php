<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-cyan-50 text-2xl text-[var(--rimis-primary-dark)]">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-[var(--rimis-primary)]">Confirma tu cuenta</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Revisa tu correo electrónico</h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Enviamos un enlace de confirmación a <strong class="text-slate-700">{{ auth()->user()->email }}</strong>.
            Debes abrirlo para verificar que tu cuenta está operativa y acceder al área privada.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <i class="fa-solid fa-circle-check mr-1"></i> Enviamos un nuevo enlace de confirmación.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
        @csrf
        <button class="w-full rounded-lg bg-[var(--rimis-primary)] px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[var(--rimis-primary-dark)]">
            Reenviar enlace de confirmación
        </button>
    </form>

    <a href="{{ route('profile.edit') }}" class="mt-3 block w-full rounded-lg border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Corregir correo electrónico
    </a>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Cerrar sesión
        </button>
    </form>
</x-guest-layout>
