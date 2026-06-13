<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div><h1 class="m-0"><i class="fas fa-file-alt text-primary mr-2"></i>Páginas</h1><small class="text-muted">Administra las páginas informativas del sitio.</small></div>
            @can('pages.create')<a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Crear página</a>@endcan
        </div>
    </x-slot>
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.pages.index') }}" class="row align-items-end">
                <div class="col-md-6"><label class="small text-muted">Buscar</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Título o slug"></div>
                <div class="col-md-3"><label class="small text-muted">Estado</label><select name="status" class="form-control"><option value="">Todos</option><option value="draft" @selected(request('status') === 'draft')>Borradores</option><option value="published" @selected(request('status') === 'published')>Publicadas</option></select></div>
                <div class="col-md-3 mt-2 mt-md-0"><button class="btn btn-primary mr-1"><i class="fas fa-filter mr-1"></i> Filtrar</button><a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div>
            </form>
        </div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="thead-light"><tr><th>Página</th><th>Estado</th><th>Autor</th><th>Actualización</th><th class="text-right">Acciones</th></tr></thead>
            <tbody>
                @forelse($pages as $page)
                    <tr>
                        <td><strong>{{ $page->title }}</strong><small class="d-block text-muted">/pagina/{{ $page->slug }}</small></td>
                        <td><span class="badge badge-{{ $page->isPublished() ? 'success' : 'secondary' }}">{{ $page->isPublished() ? 'Publicada' : 'Borrador' }}</span></td>
                        <td>{{ $page->author?->name ?? 'Sin autor' }}</td>
                        <td class="text-muted">{{ $page->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right text-nowrap">
                            @if($page->isPublished())<a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>@endif
                            @can('pages.edit')<a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>@endcan
                            @can('pages.publish')
                                <form method="POST" action="{{ $page->isPublished() ? route('admin.pages.unpublish', $page) : route('admin.pages.publish', $page) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $page->isPublished() ? 'warning' : 'success' }}" title="{{ $page->isPublished() ? 'Despublicar' : 'Publicar' }}"><i class="fas fa-{{ $page->isPublished() ? 'eye-slash' : 'globe' }}"></i></button></form>
                            @endcan
                            @can('pages.delete')<form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta página definitivamente?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5"><i class="far fa-file-alt fa-3x text-muted mb-3"></i><h5>No hay páginas</h5></td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        @if($pages->hasPages())<div class="card-footer">{{ $pages->links() }}</div>@endif
    </div>
</x-app-layout>
