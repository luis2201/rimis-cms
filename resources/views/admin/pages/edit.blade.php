<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div><h1 class="m-0"><i class="fas fa-file-alt text-primary mr-2"></i>Editar página</h1><small class="text-muted">{{ $page->title }}</small></div>
            @if($page->isPublished())<a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt mr-1"></i> Ver página</a>@endif
        </div>
    </x-slot>
    <div class="row">
        <div class="col-lg-9">
            <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="card card-primary card-outline shadow-sm">
                @csrf @method('PUT')
                <div class="card-body">@include('admin.pages._form')</div>
                <div class="card-footer"><button class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i> Guardar cambios</button><a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Volver</a></div>
            </form>
        </div>
        <div class="col-lg-3">
            <div class="card card-outline card-{{ $page->isPublished() ? 'success' : 'secondary' }}">
                <div class="card-header"><h3 class="card-title">Publicación</h3></div>
                <div class="card-body">
                    <p><span class="badge badge-{{ $page->isPublished() ? 'success' : 'secondary' }}">{{ $page->isPublished() ? 'Publicada' : 'Borrador' }}</span></p>
                    @if($page->published_at)<small class="text-muted d-block mb-3">Publicada: {{ $page->published_at->format('d/m/Y H:i') }}</small>@endif
                    @can('pages.publish')
                        @if($page->isPublished())
                            <form method="POST" action="{{ route('admin.pages.unpublish', $page) }}">@csrf @method('PATCH')<button class="btn btn-warning btn-block"><i class="fas fa-eye-slash mr-1"></i> Despublicar</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.pages.publish', $page) }}">@csrf @method('PATCH')<button class="btn btn-success btn-block"><i class="fas fa-globe mr-1"></i> Publicar</button></form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline shadow-sm mt-4">
        <div class="card-header d-flex align-items-center">
            <div><h3 class="card-title mb-0"><i class="fas fa-cubes mr-2"></i>Constructor de bloques</h3><small class="d-block text-muted mt-1">Agrega secciones reutilizables debajo del contenido principal.</small></div>
            <button class="btn btn-primary ml-auto" type="button" data-toggle="modal" data-target="#create-page-block"><i class="fas fa-plus mr-1"></i> Agregar bloque</button>
        </div>
        <div class="card-body">
            @forelse($page->blocks as $block)
                @include('admin.pages._block-row')
            @empty
                <div class="text-center py-5"><i class="fas fa-cubes fa-3x text-muted mb-3"></i><h5>Esta página aún no tiene bloques</h5><p class="text-muted mb-0">Usa “Agregar bloque” para comenzar a construirla.</p></div>
            @endforelse
        </div>
    </div>

    <div class="modal fade page-block-modal" id="create-page-block" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('admin.pages.blocks.store', $page) }}" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-cube mr-2"></i>Agregar bloque</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">@include('admin.pages._block-fields', ['block' => new \App\Models\PageBlock(['type' => 'hero', 'is_active' => true])])</div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Agregar bloque</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
