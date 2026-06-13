<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-newspaper text-primary mr-2"></i>Crear noticia</h1></x-slot>
    <form method="POST" action="{{ route('admin.news.store') }}" class="card card-primary card-outline shadow-sm">@csrf<div class="card-body">@include('admin.news._form')</div><div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar borrador</button><a href="{{ route('admin.news.index') }}" class="btn btn-secondary ml-1">Cancelar</a></div></form>
</x-app-layout>
