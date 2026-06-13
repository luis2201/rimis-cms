<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div><h1 class="m-0"><i class="fas fa-stream text-primary mr-2"></i>{{ $menu->name }}</h1><small class="text-muted">Crea submenús y define el orden de navegación.</small></div>
            @can('menus.create')<button class="btn btn-primary" data-toggle="modal" data-target="#create-menu-item-modal"><i class="fas fa-plus mr-1"></i> Crear ítem</button>@endcan
        </div>
    </x-slot>
    <div class="row">
        <div class="col-lg-9">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sort mr-2"></i>Ítems y submenús</h3><span class="float-right text-muted small">Usa las flechas para ordenar cada nivel</span></div>
                <div class="card-body">
                    @forelse($menu->rootItems as $item)
                        @include('admin.menus._item-row', ['item' => $item, 'level' => 0])
                    @empty
                        <div class="text-center py-5"><i class="fas fa-list fa-3x text-muted mb-3"></i><h5>Este menú aún no tiene ítems</h5></div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-3"><div class="callout callout-info"><h5>Ubicación</h5><p>{{ \App\Models\Menu::LOCATIONS[$menu->location] }}</p><h5>Estado</h5><span class="badge badge-{{ $menu->is_active ? 'success' : 'secondary' }}">{{ $menu->is_active ? 'Activo' : 'Inactivo' }}</span></div></div>
    </div>

    <div class="modal fade" id="create-menu-item-modal"><div class="modal-dialog"><form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="modal-content">@csrf<div class="modal-header"><h5 class="modal-title">Crear ítem</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('admin.menus._item-fields')</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button class="btn btn-primary">Crear ítem</button></div></form></div></div>
    <div class="modal fade" id="edit-menu-item-modal"><div class="modal-dialog"><form method="POST" action="#" class="modal-content" id="edit-menu-item-form">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">Editar ítem</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('admin.menus._item-fields')</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar cambios</button></div></form></div></div>
</x-app-layout>
