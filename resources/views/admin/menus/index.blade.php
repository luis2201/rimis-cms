<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div><h1 class="m-0"><i class="fas fa-sitemap text-primary mr-2"></i>Menús dinámicos</h1><small class="text-muted">Administra la navegación pública de RIMIS.</small></div>
            @can('menus.create')<a href="{{ route('admin.menus.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Crear menú</a>@endcan
        </div>
    </x-slot>
    <div class="row">
        @forelse($menus as $menu)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card card-primary card-outline h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between"><h4>{{ $menu->name }}</h4><span class="badge badge-{{ $menu->is_active ? 'success' : 'secondary' }}">{{ $menu->is_active ? 'Activo' : 'Inactivo' }}</span></div>
                        <p class="text-muted">{{ $menu->description ?: 'Sin descripción.' }}</p>
                        <span class="badge badge-light border"><i class="fas fa-map-marker-alt mr-1"></i>{{ \App\Models\Menu::LOCATIONS[$menu->location] }}</span>
                        <span class="badge badge-light border">{{ $menu->items_count }} ítems</span>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.menus.show', $menu) }}" class="btn btn-sm btn-primary"><i class="fas fa-list mr-1"></i> Ítems</a>
                        @can('menus.edit')<a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>@endcan
                        @can('menus.delete')<form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este menú y todos sus ítems?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="callout callout-info">Aún no existen menús.</div></div>
        @endforelse
    </div>
</x-app-layout>
