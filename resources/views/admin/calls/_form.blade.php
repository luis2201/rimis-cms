@isset($call) @if($call->isResearcherSubmission())<div class="alert alert-info"><strong>Contenido enviado por un investigador.</strong> Remitente: {{ $call->author?->name }} · Revisión: {{ $call->reviewStatusLabel() }}</div>@endif @endisset
<div class="row">
    <div class="form-group col-md-8">
        <label>Título <span class="text-danger">*</span></label>
        <input name="title" class="form-control" required maxlength="255" value="{{ old('title', $call->title ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Imagen destacada</label>
        <select name="featured_image_id" class="form-control">
            <option value="">Sin imagen</option>
            @foreach($mediaImages as $image)
                <option value="{{ $image->id }}" @selected((int) old('featured_image_id', $call->featured_image_id ?? 0) === $image->id)>{{ $image->original_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label>Slug</label>
    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">/convocatorias/</span></div><input name="slug" class="form-control" value="{{ old('slug', $call->slug ?? '') }}" placeholder="Se genera desde el título"></div>
</div>
<div class="form-group">
    <label>Resumen</label>
    <textarea name="summary" maxlength="1000" rows="3" class="form-control" placeholder="Descripción breve para el listado público">{{ old('summary', $call->summary ?? '') }}</textarea>
</div>

<h5 class="border-bottom pb-2 mt-4"><i class="far fa-calendar-alt text-primary mr-2"></i>Fechas y estado</h5>
<div class="row">
    <div class="form-group col-md-6">
        <label>Apertura <span class="text-danger">*</span></label>
        <input type="datetime-local" name="opens_at" class="form-control" required value="{{ old('opens_at', isset($call) ? $call->opens_at?->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>Cierre <span class="text-danger">*</span></label>
        <input type="datetime-local" name="closes_at" class="form-control" required value="{{ old('closes_at', isset($call) ? $call->closes_at?->format('Y-m-d\TH:i') : '') }}">
    </div>
</div>
<div class="form-group">
    <label>Bases PDF @if(!isset($call))<span class="text-danger">*</span>@endif</label>
    <div class="custom-file"><input type="file" name="bases_pdf" class="custom-file-input" id="call-bases-pdf" accept="application/pdf" @required(!isset($call))><label class="custom-file-label" for="call-bases-pdf">{{ isset($call) ? 'Reemplazar bases actuales' : 'Seleccionar bases PDF' }}</label></div>
    <small class="text-muted">Formato PDF, máximo 20 MB.@isset($call) Actual: {{ $call->bases_pdf_original_name }} ({{ $call->formattedBasesSize() }})@endisset</small>
</div>

<h5 class="border-bottom pb-2 mt-4"><i class="fas fa-user-check text-primary mr-2"></i>Inscripciones</h5>
<div class="custom-control custom-checkbox mb-3">
    <input type="hidden" name="registration_enabled" value="0">
    <input type="checkbox" class="custom-control-input" id="registration_enabled" name="registration_enabled" value="1" @checked(old('registration_enabled', $call->registration_enabled ?? false))>
    <label class="custom-control-label" for="registration_enabled">Habilitar enlace de inscripción mientras la convocatoria esté abierta</label>
</div>
<div class="form-group">
    <label>Enlace de inscripción</label>
    <input type="url" name="registration_url" class="form-control" maxlength="2048" value="{{ old('registration_url', $call->registration_url ?? '') }}" placeholder="https://">
    <small class="text-muted">Puede dirigir a un formulario externo o plataforma de postulaciones.</small>
</div>

<div class="form-group mt-4">
    <label>Descripción completa <span class="text-danger">*</span></label>
    <textarea name="description" rows="18" required class="form-control page-content-editor" data-image-list-url="{{ route('admin.media-files.editor.images') }}" data-image-upload-url="{{ route('admin.media-files.editor.upload') }}">{{ old('description', $call->description ?? '') }}</textarea>
</div>

<div class="modal fade" id="page-media-library-modal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="far fa-images mr-2"></i>Biblioteca multimedia RIMIS</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row" id="page-media-library-grid"></div><div class="text-center py-5 d-none" id="page-media-library-empty">No hay imágenes activas disponibles.</div></div></div></div></div>
