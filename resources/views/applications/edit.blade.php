<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-edit text-primary mr-2"></i>Editar postulación</h1></x-slot>
    <div class="card card-primary card-outline"><div class="card-body">
        @if($application->isObserved())<div class="alert alert-warning"><strong>Observaciones:</strong> {{ $application->review_notes }}</div>@endif
        <form method="POST" action="{{ route('applications.update') }}">@include('applications._form')</form>
    </div></div>
</x-app-layout>
