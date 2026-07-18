<x-app-layout>
    <x-slot name="header"><div class="d-flex justify-content-between align-items-center"><div><h1 class="m-0"><i class="fas fa-bullhorn text-primary mr-2"></i>Convocatorias</h1><small class="text-muted">Gestiona bases, fechas e inscripciones.</small></div>@can('calls.create')<a href="{{ route('admin.calls.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Crear convocatoria</a>@endcan</div></x-slot>
    @include('admin.partials.content-review-filter', ['model' => \App\Models\CallForProposal::class])
    <div class="card card-primary card-outline">
        <div class="card-header"><form class="row"><div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar convocatoria"></div><div class="col-md-3"><select name="status" class="form-control"><option value="">Todos los estados</option><option value="draft" @selected(request('status') === 'draft')>Borradores</option><option value="published" @selected(request('status') === 'published')>Publicadas</option></select></div><div class="col-md-3"><button class="btn btn-primary">Filtrar</button></div></form></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Convocatoria</th><th>Fechas</th><th>Estado</th><th>Inscripciones</th><th class="text-right">Acciones</th></tr></thead><tbody>
            @forelse($calls as $item)
                <tr>
                    <td><strong>{{ $item->title }}</strong><small class="d-block text-muted">/convocatorias/{{ $item->slug }}</small></td>
                    <td><small class="d-block">Abre: {{ $item->opens_at->format('d/m/Y H:i') }}</small><small class="d-block">Cierra: {{ $item->closes_at->format('d/m/Y H:i') }}</small></td>
                    <td><span class="badge badge-{{ $item->isPublished() ? 'success' : 'secondary' }}">{{ $item->isPublished() ? 'Publicada' : 'Borrador' }}</span><span class="badge badge-{{ $item->operationalStatusColor() }} ml-1">{{ $item->operationalStatusLabel() }}</span></td>
                    <td><span class="badge badge-{{ $item->registration_enabled ? 'primary' : 'light' }}">{{ $item->registration_enabled ? 'Habilitadas' : 'Deshabilitadas' }}</span></td>
                    <td class="text-right text-nowrap">@can('calls.publish')<form method="POST" action="{{ $item->isPublished() ? route('admin.calls.unpublish', $item) : route('admin.calls.publish', $item) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-{{ $item->isPublished() ? 'secondary' : 'success' }}" title="{{ $item->isPublished() ? 'Despublicar' : 'Publicar' }}"><i class="fas fa-{{ $item->isPublished() ? 'eye-slash' : 'globe' }}"></i></button></form>@endcan @can('calls.edit')<a href="{{ route('admin.calls.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>@endcan @can('calls.delete')<form method="POST" action="{{ route('admin.calls.destroy', $item) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta convocatoria y sus bases PDF?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endcan</td>
                </tr>
            @empty<tr><td colspan="5" class="text-center py-5">No hay convocatorias.</td></tr>@endforelse
        </tbody></table></div></div>
        @if($calls->hasPages())<div class="card-footer">{{ $calls->links() }}</div>@endif
    </div>
</x-app-layout>
