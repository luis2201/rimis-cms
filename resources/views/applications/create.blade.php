<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-file-signature text-primary mr-2"></i>Iniciar postulación</h1></x-slot>
    <div class="card card-primary card-outline"><div class="card-body">
        <p class="text-muted">Puedes guardar un borrador aunque tu perfil todavía no esté completo.</p>
        <form method="POST" action="{{ route('applications.store') }}">@include('applications._form')</form>
    </div></div>
</x-app-layout>
