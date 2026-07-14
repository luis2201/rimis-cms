<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-user text-primary mr-2"></i>Mi espacio RIMIS</h1>
    </x-slot>

    <div class="callout callout-info">
        <h5><i class="fas fa-info-circle mr-1"></i> Bienvenido, {{ Auth::user()->name }}</h5>
        <p class="mb-0">Tu cuenta está activa, pero todavía no perteneces a la Red de Investigadores RIMIS.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline h-100">
                <div class="card-header"><h3 class="card-title">Perfil profesional</h3></div>
                <div class="card-body">
                    <p>Completa y mantén actualizada tu información profesional para preparar una futura postulación.</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-user-edit mr-1"></i> Completar perfil
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-secondary card-outline h-100">
                <div class="card-header"><h3 class="card-title">Postulación a la red</h3></div>
                <div class="card-body">
                    <p>Próximamente podrás enviar y consultar desde aquí tu postulación para pertenecer a RIMIS.</p>
                    <button class="btn btn-secondary" disabled>Postulación próximamente</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
