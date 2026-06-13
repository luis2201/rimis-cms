<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-tachometer-alt text-primary mr-2"></i>Dashboard administrativo</h1>
    </x-slot>

    <div class="row">
        @can('media.view')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\MediaFile::count() }}</h3>
                        <p>Archivos multimedia</p>
                    </div>
                    <div class="icon"><i class="far fa-images"></i></div>
                    <a href="{{ route('admin.media-files.index') }}" class="small-box-footer">
                        Administrar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\MediaFile::where('status', true)->count() }}</h3>
                        <p>Archivos activos</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <a href="{{ route('admin.media-files.index') }}" class="small-box-footer">
                        Ver biblioteca <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        @can('users.view')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\User::count() }}</h3>
                        <p>Usuarios registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                        Administrar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>CMS</h3>
                    <p>Estado del sistema</p>
                </div>
                <div class="icon"><i class="fas fa-layer-group"></i></div>
                <span class="small-box-footer">En construcción</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-rocket mr-2"></i>Accesos rápidos</h3>
                </div>
                <div class="card-body">
                    @can('media.create')
                        <a href="{{ route('admin.media-files.create') }}" class="btn btn-primary mr-2 mb-2">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Subir archivo
                        </a>
                    @endcan
                    @can('media.view')
                        <a href="{{ route('admin.media-files.index') }}" class="btn btn-outline-primary mr-2 mb-2">
                            <i class="far fa-images mr-1"></i> Biblioteca multimedia
                        </a>
                    @endcan
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary mb-2">
                        <i class="fas fa-user-cog mr-1"></i> Mi perfil
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle mr-1"></i> Bienvenido, {{ Auth::user()->name }}</h5>
                <p class="mb-0">Desde este panel podrás administrar las áreas autorizadas del sitio RIMIS.</p>
            </div>
        </div>
    </div>
</x-app-layout>
