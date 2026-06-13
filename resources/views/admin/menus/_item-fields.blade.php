<div class="form-group"><label>Etiqueta <span class="text-danger">*</span></label><input name="label" class="form-control" required></div>
<div class="form-group"><label>URL <span class="text-danger">*</span></label><input name="url" class="form-control" value="#" required><small class="text-muted">Puede ser una URL completa o una ruta como /eventos.</small></div>
@if(isset($publishedPages) && $publishedPages->isNotEmpty())
    <div class="form-group">
        <label>Usar página publicada</label>
        <select class="form-control menu-page-url">
            <option value="">Selecciona una página</option>
            @foreach($publishedPages as $publishedPage)
                <option value="{{ route('pages.show', $publishedPage->slug, false) }}">{{ $publishedPage->title }}</option>
            @endforeach
        </select>
    </div>
@endif
<div class="row">
    <div class="form-group col-md-6">
        <label>Icono Font Awesome</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text menu-icon-preview"><i class="fa-solid fa-icons"></i></span>
            </div>
            <input name="icon" class="form-control menu-icon-input" list="menu-icon-options" placeholder="fa-solid fa-house">
        </div>
        <small class="text-muted">Usa clases de Font Awesome 6, por ejemplo <code>fa-solid fa-house</code>.</small>
    </div>
    <div class="form-group col-md-6"><label>Abrir en</label><select name="target" class="form-control"><option value="_self">Misma ventana</option><option value="_blank">Nueva ventana</option></select></div>
</div>
<datalist id="menu-icon-options">
    <option value="fa-solid fa-house">Inicio</option>
    <option value="fa-solid fa-circle-info">Información</option>
    <option value="fa-solid fa-users">Usuarios</option>
    <option value="fa-solid fa-flask">Investigación</option>
    <option value="fa-solid fa-newspaper">Noticias</option>
    <option value="fa-solid fa-calendar-days">Eventos</option>
    <option value="fa-solid fa-envelope">Contacto</option>
    <option value="fa-brands fa-facebook">Facebook</option>
    <option value="fa-brands fa-x-twitter">X</option>
    <option value="fa-brands fa-instagram">Instagram</option>
    <option value="fa-brands fa-linkedin">LinkedIn</option>
    <option value="fa-brands fa-youtube">YouTube</option>
</datalist>
<div class="form-group"><label>Ítem padre</label><select name="parent_id" class="form-control"><option value="">Sin padre, nivel principal</option>@foreach($parentItems as $parent)<option value="{{ $parent->id }}">{{ $parent->label }}</option>@endforeach</select></div>
<div class="form-group mb-0"><label>Estado</label><select name="is_active" class="form-control"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
