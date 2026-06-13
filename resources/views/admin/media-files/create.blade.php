<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-cloud-upload-alt text-primary mr-2"></i>Subir archivo</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
        <form action="{{ route('admin.media-files.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="card card-primary card-outline shadow-sm">

            @csrf
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-upload mr-2"></i>Información del archivo</h3>
            </div>
            <div class="card-body">

            <div class="mb-3">
                <label>Archivo <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" required>
                <small class="text-muted">
                    Formatos: jpg, png, webp, pdf, doc, docx, xls, xlsx, ppt, pptx. Máximo 10MB.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Texto alternativo</label>
                <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary mr-2">
                    <i class="fas fa-save mr-1"></i> Guardar
                </button>

                <a href="{{ route('admin.media-files.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
            </div>
        </form>
        </div>
        <div class="col-lg-4">
            <div class="callout callout-info">
                <h5><i class="fas fa-lightbulb mr-1"></i> Recomendaciones</h5>
                <p>Usa nombres descriptivos y agrega texto alternativo a las imágenes para mejorar la accesibilidad.</p>
                <p class="mb-0"><strong>Límite:</strong> 10 MB por archivo.</p>
            </div>
        </div>
    </div>
</x-app-layout>
