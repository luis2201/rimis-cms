<x-guest-layout wide>
    <header class="mx-auto max-w-2xl text-center">
        <span class="subscription-kicker">Membresía RIMIS</span>
        <h1 class="subscription-title mt-3">Elige tu tipo de suscripción</h1>
        <p class="mt-4 text-base leading-7 text-slate-600">Forma parte de una red que conecta conocimiento, investigación e instituciones.</p>
    </header>

    <div class="mt-10 grid gap-6 md:grid-cols-2">
        <article class="subscription-option subscription-option-professional">
            <div class="subscription-icon"><i class="fa-solid fa-user-graduate"></i></div>
            <span class="subscription-number">01</span>
            <h2>Suscripción profesional</h2>
            <p>Para investigadores y profesionales que desean integrarse, publicar y compartir su trabajo con la red.</p>
            <ul>
                <li><i class="fa-solid fa-check"></i> Perfil en el directorio profesional</li>
                <li><i class="fa-solid fa-check"></i> Publicación de aportes e investigaciones</li>
                <li><i class="fa-solid fa-check"></i> Certificación de membresía</li>
            </ul>
            <a class="subscription-action" href="{{ route('subscriptions.create', 'professional') }}">Iniciar suscripción <i class="fa-solid fa-arrow-right"></i></a>
        </article>

        <article class="subscription-option subscription-option-institutional">
            <div class="subscription-icon"><i class="fa-solid fa-building-columns"></i></div>
            <span class="subscription-number">02</span>
            <h2>Suscripción institucional</h2>
            <p>Para instituciones públicas, privadas, ONG y organizaciones que impulsan la investigación.</p>
            <ul>
                <li><i class="fa-solid fa-check"></i> Presencia en el directorio institucional</li>
                <li><i class="fa-solid fa-check"></i> Las mismas funciones de publicación</li>
                <li><i class="fa-solid fa-check"></i> Certificación de membresía</li>
            </ul>
            <a class="subscription-action" href="{{ route('subscriptions.create', 'institutional') }}">Iniciar suscripción <i class="fa-solid fa-arrow-right"></i></a>
        </article>
    </div>

    <p class="mt-8 text-center text-sm text-slate-500"><i class="fa-solid fa-shield-halved mr-2 text-[var(--rimis-primary)]"></i>La solicitud será revisada antes de habilitar el acceso a la plataforma.</p>
</x-guest-layout>
