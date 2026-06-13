<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-edit text-primary mr-2"></i>Editar archivo</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
        <form action="{{ route('admin.media-files.update', $mediaFile) }}"
              method="POST"
              class="card card-primary card-outline shadow-sm">

            @csrf
            @method('PUT')
            <div class="card-body">

            <div class="mb-3">
                <label class="form-label">Archivo</label><br>

                @if($mediaFile->file_type === 'image')
                    <img src="{{ $mediaFile->publicUrl() }}"
                         style="max-width:300px; border-radius:8px;"
                         alt="{{ $mediaFile->alt_text }}">
                @else
                    <a href="{{ $mediaFile->publicUrl() }}" target="_blank">
                        Ver archivo
                    </a>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Texto alternativo</label>
                <input type="text"
                       name="alt_text"
                       class="form-control"
                       value="{{ old('alt_text', $mediaFile->alt_text) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description"
                          class="form-control"
                          rows="4">{{ old('description', $mediaFile->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $mediaFile->status ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ !$mediaFile->status ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary mr-2">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>

                <a href="{{ route('admin.media-files.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
            </div>

        </form>
        </div>
    </div>
</x-app-layout>
