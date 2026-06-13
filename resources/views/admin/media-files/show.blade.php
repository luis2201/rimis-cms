<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-file-alt text-primary mr-2"></i>Detalle del archivo</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body">

            <div class="mb-3">
                @if($mediaFile->file_type === 'image')
                    <img src="{{ $mediaFile->publicUrl() }}"
                         style="max-width:400px; border-radius:8px;"
                         alt="{{ $mediaFile->alt_text }}">
                @else
                    <a href="{{ $mediaFile->publicUrl() }}" target="_blank" class="btn btn-outline-primary">
                        Abrir archivo
                    </a>
                @endif
            </div>

            <p><strong>Nombre original:</strong> {{ $mediaFile->original_name }}</p>
            <p><strong>Tipo:</strong> {{ $mediaFile->file_type }}</p>
            <p><strong>MIME:</strong> {{ $mediaFile->mime_type }}</p>
            <p><strong>Tamaño:</strong> {{ number_format($mediaFile->size / 1024, 2) }} KB</p>
            <p><strong>Ruta:</strong> {{ $mediaFile->file_path }}</p>
            <p><strong>Texto alternativo:</strong> {{ $mediaFile->alt_text }}</p>
            <p><strong>Descripción:</strong> {{ $mediaFile->description }}</p>
            </div>

            <div class="card-footer d-flex align-items-center">
                @can('media.edit')
                    <a href="{{ route('admin.media-files.edit', $mediaFile) }}" class="btn btn-primary mr-2">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                @endcan

                @can('media.delete')
                    <form action="{{ route('admin.media-files.destroy', $mediaFile) }}"
                          method="POST"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este archivo?')">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger mr-2">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </form>
                @endcan

                <a href="{{ route('admin.media-files.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>

        </div>
        </div>
    </div>
</x-app-layout>
