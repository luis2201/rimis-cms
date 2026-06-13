<div class="row">
    <div class="form-group col-md-8">
        <label>Título <span class="text-danger">*</span></label>
        <input name="title" class="form-control" required maxlength="255" value="{{ old('title', $event->title ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Imagen destacada</label>
        <select name="featured_image_id" class="form-control">
            <option value="">Sin imagen</option>
            @foreach($mediaImages as $image)
                <option value="{{ $image->id }}" @selected((int) old('featured_image_id', $event->featured_image_id ?? 0) === $image->id)>{{ $image->original_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label>Slug</label>
    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">/eventos/</span></div><input name="slug" class="form-control" value="{{ old('slug', $event->slug ?? '') }}" placeholder="Se genera desde el título"></div>
</div>
<div class="form-group">
    <label>Resumen</label>
    <textarea name="summary" maxlength="1000" rows="3" class="form-control" placeholder="Descripción breve para el listado público">{{ old('summary', $event->summary ?? '') }}</textarea>
</div>

<h5 class="border-bottom pb-2 mt-4"><i class="far fa-calendar-alt text-primary mr-2"></i>Programación</h5>
<div class="row">
    <div class="form-group col-md-4">
        <label>Fecha y hora de inicio <span class="text-danger">*</span></label>
        <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at', isset($event) ? $event->starts_at?->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Fecha y hora de fin <span class="text-danger">*</span></label>
        <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at', isset($event) ? $event->ends_at?->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Modalidad <span class="text-danger">*</span></label>
        <select name="modality" class="form-control" required>
            <option value="in_person" @selected(old('modality', $event->modality ?? 'in_person') === 'in_person')>Presencial</option>
            <option value="virtual" @selected(old('modality', $event->modality ?? '') === 'virtual')>Virtual</option>
            <option value="hybrid" @selected(old('modality', $event->modality ?? '') === 'hybrid')>Híbrido</option>
        </select>
    </div>
</div>
<div class="form-group">
    <label>Ubicación o plataforma</label>
    <input name="location" class="form-control" maxlength="255" value="{{ old('location', $event->location ?? '') }}" placeholder="Dirección, auditorio o plataforma virtual">
</div>

<h5 class="border-bottom pb-2 mt-4"><i class="fas fa-address-card text-primary mr-2"></i>Organización y contactos</h5>
<div class="row">
    <div class="form-group col-md-6"><label>Organizador</label><input name="organizer" class="form-control" maxlength="255" value="{{ old('organizer', $event->organizer ?? '') }}"></div>
    <div class="form-group col-md-6"><label>Responsable</label><input name="responsible_name" class="form-control" maxlength="255" value="{{ old('responsible_name', $event->responsible_name ?? '') }}"></div>
    <div class="form-group col-md-4"><label>Correo de contacto</label><input type="email" name="contact_email" class="form-control" maxlength="255" value="{{ old('contact_email', $event->contact_email ?? '') }}"></div>
    <div class="form-group col-md-4"><label>Teléfono de contacto</label><input name="contact_phone" class="form-control" maxlength="50" value="{{ old('contact_phone', $event->contact_phone ?? '') }}"></div>
    <div class="form-group col-md-4"><label>Enlace informativo</label><input type="url" name="website_url" class="form-control" maxlength="2048" value="{{ old('website_url', $event->website_url ?? '') }}" placeholder="https://"></div>
</div>

<div class="form-group mt-3">
    <label>Descripción completa <span class="text-danger">*</span></label>
    <textarea name="description" rows="18" required class="form-control page-content-editor" data-image-list-url="{{ route('admin.media-files.editor.images') }}" data-image-upload-url="{{ route('admin.media-files.editor.upload') }}">{{ old('description', $event->description ?? '') }}</textarea>
</div>

<div class="modal fade" id="page-media-library-modal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="far fa-images mr-2"></i>Biblioteca multimedia RIMIS</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row" id="page-media-library-grid"></div><div class="text-center py-5 d-none" id="page-media-library-empty">No hay imágenes activas disponibles.</div></div></div></div></div>
