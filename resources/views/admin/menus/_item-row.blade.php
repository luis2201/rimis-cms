<div class="menu-item-row ml-{{ min($level * 3, 5) }} mb-2">
    <div class="card mb-0">
        <div class="card-body py-2 d-flex flex-wrap align-items-center">
            <span class="menu-item-handle mr-2"><i class="fas fa-grip-vertical"></i></span>
            @if($item->icon)<i class="{{ $item->icon }} text-primary mr-2"></i>@endif
            <div class="mr-auto"><strong>{{ $item->label }}</strong><small class="d-block text-muted">{{ $item->url }}</small></div>
            <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }} mr-2">{{ $item->is_active ? 'Activo' : 'Inactivo' }}</span>
            @can('menus.edit')
                <form method="POST" action="{{ route('admin.menus.items.move', [$menu, $item, 'up']) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-xs btn-outline-secondary" title="Subir"><i class="fas fa-arrow-up"></i></button></form>
                <form method="POST" action="{{ route('admin.menus.items.move', [$menu, $item, 'down']) }}" class="d-inline mr-1">@csrf @method('PATCH')<button class="btn btn-xs btn-outline-secondary" title="Bajar"><i class="fas fa-arrow-down"></i></button></form>
                <button class="btn btn-xs btn-outline-primary edit-menu-item" type="button" data-toggle="modal" data-target="#edit-menu-item-modal" data-action="{{ route('admin.menus.items.update', [$menu, $item]) }}" data-label="{{ $item->label }}" data-url="{{ $item->url }}" data-icon="{{ $item->icon }}" data-target-value="{{ $item->target }}" data-parent="{{ $item->parent_id }}" data-active="{{ (int) $item->is_active }}"><i class="fas fa-edit"></i></button>
            @endcan
            @can('menus.delete')<form method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" class="d-inline ml-1" onsubmit="return confirm('¿Eliminar este ítem y sus submenús?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endcan
        </div>
    </div>
</div>
@foreach($item->children as $child)
    @include('admin.menus._item-row', ['item' => $child, 'level' => $level + 1])
@endforeach
