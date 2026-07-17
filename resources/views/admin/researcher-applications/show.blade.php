<x-app-layout>
<x-slot name="header"><h1 class="m-0"><i class="fas fa-file-alt text-primary mr-2"></i>Postulación de {{ $application->user->name }}</h1></x-slot>
<div class="row"><div class="col-lg-8">
<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title">Estado: {{ $application::STATUS_LABELS[$application->status] }}</h3></div><div class="card-body">
<h5>Datos presentados</h5><dl class="row">@foreach(($application->profile_snapshot ?: []) as $key=>$value)<dt class="col-sm-4">{{ str_replace('_',' ',ucfirst($key)) }}</dt><dd class="col-sm-8">{{ $value }}</dd>@endforeach</dl>
<hr><h5>Postulación</h5><p><strong>Motivación:</strong><br>{{ $application->motivation }}</p><p><strong>Experiencia:</strong><br>{{ $application->experience_summary }}</p><p><strong>Contribución:</strong><br>{{ $application->expected_contribution }}</p>
@if($application->review_notes)<div class="alert alert-info"><strong>Notas:</strong> {{ $application->review_notes }}</div>@endif
</div></div>
<div class="card"><div class="card-header"><h3 class="card-title">Historial completo</h3></div><div class="card-body">@include('applications._history')</div></div>
</div><div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Perfil profesional</h3></div><div class="card-body"><p><strong>{{ $application->user->name }}</strong><br>{{ $application->user->email }}</p><p>{{ $application->user->researcherProfile?->institution }}<br>{{ $application->user->researcherProfile?->research_area }}</p><a class="btn btn-outline-primary btn-block" href="{{ route('admin.researcher-applications.cv',$application) }}">Descargar currículum</a></div></div>
<div class="card"><div class="card-header"><h3 class="card-title">Acciones</h3></div><div class="card-body">
@if($application->isSubmitted())<form method="POST" action="{{ route('admin.researcher-applications.start-review',$application) }}">@csrf @method('PATCH')<button class="btn btn-primary btn-block">Iniciar revisión</button></form>@endif
@if($application->isUnderReview())
<form method="POST" action="{{ route('admin.researcher-applications.observe',$application) }}" class="mb-3">@csrf @method('PATCH')<textarea name="review_notes" class="form-control mb-2" required maxlength="5000" placeholder="Observaciones"></textarea><button class="btn btn-warning btn-block">Observar</button></form>
<form method="POST" action="{{ route('admin.researcher-applications.approve',$application) }}" class="mb-3">@csrf @method('PATCH')<textarea name="review_notes" class="form-control mb-2" maxlength="5000" placeholder="Nota opcional"></textarea><button class="btn btn-success btn-block">Aprobar</button></form>
<form method="POST" action="{{ route('admin.researcher-applications.reject',$application) }}">@csrf @method('PATCH')<textarea name="review_notes" class="form-control mb-2" required maxlength="5000" placeholder="Razón del rechazo"></textarea><button class="btn btn-danger btn-block">Rechazar</button></form>
@endif
</div></div></div></div>
</x-app-layout>
