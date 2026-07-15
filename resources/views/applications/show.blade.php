<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-file-signature text-primary mr-2"></i>Mi postulación RIMIS</h1></x-slot>
    <div class="row"><div class="col-lg-8">
        <div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title">Estado: {{ $application::STATUS_LABELS[$application->status] }}</h3></div><div class="card-body">
            @if($application->isSubmitted())<div class="alert alert-info">Tu postulación está pendiente de revisión.</div>@endif
            @if($application->isUnderReview())<div class="alert alert-info">La revisión inició {{ optional($application->review_started_at)->format('d/m/Y H:i') }}.</div>@endif
            @if($application->isObserved())<div class="alert alert-warning"><strong>Observaciones:</strong> {{ $application->review_notes }}</div>@endif
            @if($application->isApproved())<div class="alert alert-success">Ya formas parte de la Red de Investigadores RIMIS.</div>@endif
            @if($application->isRejected())<div class="alert alert-danger"><strong>Razón:</strong> {{ $application->review_notes }}</div>@endif
            @if($application->isWithdrawn())<div class="alert alert-secondary">Esta postulación fue retirada y no puede reactivarse.</div>@endif
            <dl><dt>Motivación</dt><dd>{{ $application->motivation ?: 'Sin completar' }}</dd><dt>Experiencia</dt><dd>{{ $application->experience_summary ?: 'Sin completar' }}</dd><dt>Contribución esperada</dt><dd>{{ $application->expected_contribution ?: 'Sin completar' }}</dd></dl>
            <div class="mt-3">
                @can('update',$application)<a href="{{ route('applications.edit') }}" class="btn btn-primary">Editar</a>@endcan
                @can('submit',$application)<form method="POST" action="{{ route('applications.submit') }}" class="d-inline">@csrf<button class="btn btn-success">{{ $application->isObserved() ? 'Reenviar' : 'Enviar postulación' }}</button></form>@endcan
                @can('withdraw',$application)<form method="POST" action="{{ route('applications.withdraw') }}" class="d-inline" onsubmit="return confirm('¿Retirar la postulación?')">@csrf<button class="btn btn-outline-danger">Retirar</button></form>@endcan
            </div>
        </div></div>
        <div class="card"><div class="card-header"><h3 class="card-title">Historial</h3></div><div class="card-body">@include('applications._history')</div></div>
    </div><div class="col-lg-4"><div class="card"><div class="card-body"><a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-block">Mi perfil profesional</a></div></div></div></div>
</x-app-layout>
