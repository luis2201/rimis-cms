<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-plus-circle text-primary mr-2"></i>Crear menú</h1></x-slot>
    <div class="row"><div class="col-lg-8">
        <form method="POST" action="{{ route('admin.menus.store') }}" class="card card-primary card-outline shadow-sm">
            @csrf
            <div class="card-body">@include('admin.menus._form')</div>
            <div class="card-footer"><button class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i> Crear menú</button><a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">Cancelar</a></div>
        </form>
    </div></div>
</x-app-layout>
