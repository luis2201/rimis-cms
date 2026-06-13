<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-book-open text-primary mr-2"></i>Crear boletín</h1></x-slot>
    <form method="POST" action="{{ route('admin.bulletins.store') }}" enctype="multipart/form-data" class="card card-primary card-outline shadow-sm">
        @csrf
        <div class="card-body">@include('admin.bulletins._form')</div>
        <div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar borrador</button><a href="{{ route('admin.bulletins.index') }}" class="btn btn-secondary ml-1">Cancelar</a></div>
    </form>
</x-app-layout>
