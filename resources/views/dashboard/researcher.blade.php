<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-microscope text-primary mr-2"></i>Panel del investigador</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Investigación</h3>
                    <p>Mis proyectos</p>
                </div>
                <div class="icon"><i class="fas fa-microscope"></i></div>
                <span class="small-box-footer">Módulo próximamente</span>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Artículos</h3>
                    <p>Envíos científicos</p>
                </div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
                <span class="small-box-footer">Módulo próximamente</span>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>Perfil</h3>
                    <p>Información profesional</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                <a href="{{ route('profile.edit') }}" class="small-box-footer">
                    Actualizar perfil <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="callout callout-info">
        <h5><i class="fas fa-info-circle mr-1"></i> Bienvenido, {{ Auth::user()->name }}</h5>
        <p class="mb-0">Este espacio reunirá tus investigaciones, artículos enviados, eventos y notificaciones.</p>
    </div>
    @if(Auth::user()->researcherApplication)
        <div class="card card-success card-outline"><div class="card-header"><h3 class="card-title">Mi membresía RIMIS</h3></div><div class="card-body"><p>Consulta tu postulación aprobada y su historial.</p><a href="{{ route('applications.show') }}" class="btn btn-success">Ver membresía</a></div></div>
    @endif
</x-app-layout>
