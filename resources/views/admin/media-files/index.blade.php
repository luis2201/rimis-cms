<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="m-0"><i class="far fa-images text-primary mr-2"></i>Biblioteca multimedia</h1>
                <small class="text-muted">Gestiona imágenes y documentos del sitio.</small>
            </div>
            @can('media.create')
                <a href="{{ route('admin.media-files.create') }}" class="btn btn-primary mt-2 mt-sm-0">
                    <i class="fas fa-cloud-upload-alt mr-1"></i> Subir con detalles
                </a>
            @endcan
        </div>
    </x-slot>

    @can('media.create')
        <form
            action="{{ route('admin.media-files.store') }}"
            method="POST"
            enctype="multipart/form-data"
            id="media-dropzone-form"
            class="media-dropzone mb-4"
        >
            @csrf
            <input
                type="file"
                name="files[]"
                id="media-dropzone-input"
                class="d-none"
                multiple
                accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
            >
            <div class="media-dropzone-content text-center">
                <i class="fas fa-cloud-upload-alt media-dropzone-icon"></i>
                <h5 class="mb-1">Arrastra tus archivos aquí</h5>
                <p class="text-muted mb-3">o selecciona varios archivos desde tu equipo</p>
                <button type="button" class="btn btn-outline-primary" id="media-browse-button">
                    <i class="fas fa-folder-open mr-1"></i> Seleccionar archivos
                </button>
                <small class="d-block text-muted mt-2">Máximo 20 archivos, 10 MB cada uno.</small>
            </div>
            <div class="media-upload-progress d-none text-center">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <p class="mb-0 font-weight-bold">Subiendo archivos...</p>
            </div>
        </form>
    @endcan

    <div class="card card-primary card-outline">
        @can('media.delete')
            <div class="card-header d-flex flex-wrap align-items-center">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="select-all-media">
                    <label class="custom-control-label" for="select-all-media">Seleccionar todos</label>
                </div>

                <span class="badge badge-light border mr-3" id="selection-counter">0 seleccionados</span>

                <form
                    action="{{ route('admin.media-files.bulk-destroy') }}"
                    method="POST"
                    id="bulk-delete-form"
                    class="ml-auto"
                    onsubmit="return confirm('¿Seguro que deseas eliminar los archivos seleccionados?')"
                >
                    @csrf
                    @method('DELETE')
                    <div id="bulk-delete-inputs"></div>
                    <button type="submit" class="btn btn-sm btn-danger" id="bulk-delete-button" disabled>
                        <i class="fas fa-trash-alt mr-1"></i> Eliminar seleccionados
                    </button>
                </form>
            </div>
        @endcan

        <div class="card-body">
            <div class="row">
                @forelse($mediaFiles as $media)
                    @php
                        $mediaUrl = $media->publicUrl();
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <article
                            class="card media-card h-100 shadow-sm"
                            data-id="{{ $media->id }}"
                            data-url="{{ $mediaUrl }}"
                            data-name="{{ $media->original_name }}"
                            data-type="{{ $media->file_type }}"
                            data-mime="{{ $media->mime_type }}"
                            data-size="{{ number_format($media->size / 1024, 2) }} KB"
                        >
                            @can('media.delete')
                                <div class="media-select-control custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input media-checkbox" id="media-{{ $media->id }}" value="{{ $media->id }}">
                                    <label class="custom-control-label" for="media-{{ $media->id }}"><span class="sr-only">Seleccionar {{ $media->original_name }}</span></label>
                                </div>
                            @endcan

                            <button type="button" class="media-preview-trigger" aria-label="Vista previa de {{ $media->original_name }}">
                                @if($media->file_type === 'image')
                                    <img src="{{ $mediaUrl }}" class="card-img-top media-preview" alt="{{ $media->alt_text }}">
                                @else
                                    <span class="file-icon d-flex flex-column align-items-center justify-content-center bg-light text-secondary">
                                        <i class="fas fa-file-alt mb-2"></i>
                                        <small class="font-weight-bold">{{ strtoupper($media->file_type) }}</small>
                                    </span>
                                @endif
                                <span class="media-hover-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <span>Vista previa</span>
                                </span>
                            </button>

                            <div class="card-body">
                                <h6 class="card-title text-truncate w-100" title="{{ $media->original_name }}">
                                    {{ $media->original_name }}
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-weight-hanging mr-1"></i>{{ number_format($media->size / 1024, 2) }} KB
                                </small>
                                <span class="badge badge-{{ $media->status ? 'success' : 'secondary' }} float-right">
                                    {{ $media->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <div class="card-footer d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-media-url" data-url="{{ $mediaUrl }}">
                                    <i class="fas fa-copy mr-1"></i> Copiar URL
                                </button>
                                @can('media.edit')
                                    <a href="{{ route('admin.media-files.edit', $media) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state text-center py-5">
                            <i class="far fa-images fa-4x text-muted mb-3"></i>
                            <h4>Aún no hay archivos</h4>
                            <p class="text-muted">Arrastra archivos sobre la zona superior para comenzar.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{ $mediaFiles->links() }}
        </div>
    </div>

    <div class="modal fade" id="media-preview-modal" tabindex="-1" role="dialog" aria-labelledby="media-preview-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="media-preview-title">Vista previa</h5>
                        <small class="text-muted" id="media-preview-meta"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="media-preview-content"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="modal-copy-url">
                        <i class="fas fa-copy mr-1"></i> Copiar URL
                    </button>
                    <a href="#" target="_blank" class="btn btn-primary" id="modal-open-file">
                        <i class="fas fa-external-link-alt mr-1"></i> Abrir archivo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="media-toast" id="media-toast" role="status">
        <i class="fas fa-check-circle mr-2"></i><span>URL copiada</span>
    </div>
</x-app-layout>
