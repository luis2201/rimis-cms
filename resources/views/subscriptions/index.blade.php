<x-guest-layout>
    <div class="text-center mb-4"><h1 class="h3">Suscripciones RIMIS</h1><p class="text-muted">Elige el tipo de membresía que deseas solicitar.</p></div>
    <div class="row">
        <div class="col-md-6 mb-3"><div class="card h-100"><div class="card-body"><h2 class="h5">Suscripción profesional</h2><p>Para investigadores y profesionales que desean integrarse a la red.</p><a class="btn btn-primary" href="{{ route('subscriptions.create','professional') }}">Iniciar suscripción</a></div></div></div>
        <div class="col-md-6 mb-3"><div class="card h-100"><div class="card-body"><h2 class="h5">Suscripción institucional</h2><p>Para instituciones públicas, privadas, ONG y otras organizaciones.</p><a class="btn btn-primary" href="{{ route('subscriptions.create','institutional') }}">Iniciar suscripción</a></div></div></div>
    </div>
</x-guest-layout>
