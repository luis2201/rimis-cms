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
                    @if(Auth::user()->researcherApplication)
                        <p>Estado actual: <strong>{{ \App\Models\ResearcherApplication::STATUS_LABELS[Auth::user()->researcherApplication->status] }}</strong></p>
                        <a href="{{ route('applications.show') }}" class="btn btn-primary">Ver mi postulación</a>
                    @else
                        <p>Todavía no has presentado tu postulación para formar parte de la Red de Investigadores RIMIS.</p>
                        <a href="{{ route('applications.create') }}" class="btn btn-primary">Iniciar postulación</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
