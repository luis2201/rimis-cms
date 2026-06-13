<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-user-plus text-primary mr-2"></i>Crear usuario</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="card card-primary card-outline shadow-sm">
                @csrf
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Información de acceso</h3>
                </div>
                <div class="card-body">
                    @include('admin.users._form')
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Crear usuario
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle mr-1"></i> Política de roles</h5>
                <p><strong>WEBMASTER:</strong> administra contenido y elementos del sitio.</p>
                <p class="mb-0"><strong>INVESTIGADOR:</strong> accede a su panel y herramientas de investigación.</p>
            </div>
        </div>
    </div>
</x-app-layout>
