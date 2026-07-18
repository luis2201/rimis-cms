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

    @can('submissions.view-own')
        <div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title">Mis aportes editoriales</h3></div><div class="card-body"><div class="row">@foreach(['draft'=>'Borradores','submitted'=>'Enviados','under_review'=>'En revisión','observed'=>'Observados','approved'=>'Aprobados','rejected'=>'Rechazados'] as $status=>$label)<div class="col-md-2 text-center"><strong class="h3 d-block">{{ $submissionCounts[$status] ?? 0 }}</strong><span class="text-muted">{{ $label }}</span></div>@endforeach</div><a href="{{ route('researcher.submissions.index') }}" class="btn btn-primary mt-3">Gestionar mis aportes</a></div></div>
    @endcan
    @can('research-publications.view-own')
        <div class="card card-success card-outline"><div class="card-header"><h3 class="card-title">Mis publicaciones de investigación</h3></div><div class="card-body"><p><strong>{{ \App\Models\ResearchPublication::where('user_id',auth()->id())->count() }}</strong> publicaciones registradas.</p><a href="{{ route('researcher.publications.index') }}" class="btn btn-success">Gestionar publicaciones</a></div></div>
    @endcan
    @php($publicProfile=Auth::user()->researcherProfile)
    @if($publicProfile)
        <div class="card card-info card-outline"><div class="card-header"><h3 class="card-title">Mi perfil público</h3></div><div class="card-body"><p><strong>{{ $publicProfile->profile_public?'Visible':'Oculto por administración' }}</strong></p><p>{{ collect([$publicProfile->public_bio,$publicProfile->orcid,$publicProfile->research_line,$publicProfile->profile_photo_id])->filter()->count()*25 }}% de información pública complementaria completada.</p><a href="{{ route('profile.edit') }}" class="btn btn-primary">Editar perfil y privacidad</a>@if($publicProfile->canAppearInDirectory())<a href="{{ route('researchers.show',$publicProfile->public_slug) }}" class="btn btn-outline-info">Ver perfil público</a>@endif</div></div>
    @endif

    <div class="callout callout-info">
        <h5><i class="fas fa-info-circle mr-1"></i> Bienvenido, {{ Auth::user()->name }}</h5>
        <p class="mb-0">Este espacio reunirá tus investigaciones, artículos enviados, eventos y notificaciones.</p>
    </div>
    @if(Auth::user()->researcherApplication)
        <div class="card card-success card-outline"><div class="card-header"><h3 class="card-title">Mi membresía RIMIS</h3></div><div class="card-body"><p>Consulta tu postulación aprobada y su historial.</p><a href="{{ route('applications.show') }}" class="btn btn-success">Ver membresía</a></div></div>
    @endif
</x-app-layout>
