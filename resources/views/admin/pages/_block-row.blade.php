<div class="card page-block-card mb-3">
    <div class="card-body py-3 d-flex flex-wrap align-items-center">
        <span class="page-block-icon mr-3"><i class="{{ $block->typeIcon() }}"></i></span>
        <div class="mr-auto">
            <strong>{{ $block->name ?: $block->typeLabel() }}</strong>
            <small class="d-block text-muted">{{ $block->typeLabel() }} · Posición {{ $block->sort_order }}</small>
        </div>
        <span class="badge badge-{{ $block->is_active ? 'success' : 'secondary' }} mr-2">{{ $block->is_active ? 'Activo' : 'Inactivo' }}</span>
        <form method="POST" action="{{ route('admin.pages.blocks.move', [$page, $block, 'up']) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" title="Subir"><i class="fas fa-arrow-up"></i></button></form>
        <form method="POST" action="{{ route('admin.pages.blocks.move', [$page, $block, 'down']) }}" class="d-inline mr-1">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" title="Bajar"><i class="fas fa-arrow-down"></i></button></form>
        <button class="btn btn-sm btn-outline-primary mr-1" type="button" data-toggle="modal" data-target="#edit-page-block-{{ $block->id }}"><i class="fas fa-edit"></i></button>
        <form method="POST" action="{{ route('admin.pages.blocks.destroy', [$page, $block]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este bloque?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
    </div>
</div>

<div class="modal fade page-block-modal" id="edit-page-block-{{ $block->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('admin.pages.blocks.update', [$page, $block]) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title"><i class="{{ $block->typeIcon() }} mr-2"></i>Editar bloque</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">@include('admin.pages._block-fields', ['block' => $block])</div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar bloque</button></div>
        </form>
    </div>
</div>
