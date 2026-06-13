<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="far fa-calendar-alt text-primary mr-2"></i>Crear evento</h1></x-slot>
    <form method="POST" action="{{ route('admin.events.store') }}" class="card card-primary card-outline shadow-sm">@csrf<div class="card-body">@include('admin.events._form')</div><div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar borrador</button><a href="{{ route('admin.events.index') }}" class="btn btn-secondary ml-1">Cancelar</a></div></form>
</x-app-layout>
