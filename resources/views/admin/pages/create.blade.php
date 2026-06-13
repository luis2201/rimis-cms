<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-file-medical text-primary mr-2"></i>Crear página</h1></x-slot>
    <div class="row">
        <div class="col-lg-9">
            <form method="POST" action="{{ route('admin.pages.store') }}" class="card card-primary card-outline shadow-sm">
                @csrf
                <div class="card-body">@include('admin.pages._form')</div>
                <div class="card-footer"><button class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i> Guardar borrador</button><a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Cancelar</a></div>
            </form>
        </div>
        <div class="col-lg-3"><div class="callout callout-info"><h5><i class="fas fa-info-circle mr-1"></i> Borrador</h5><p class="mb-0">La página no será pública hasta usar la acción Publicar.</p></div></div>
    </div>
</x-app-layout>
