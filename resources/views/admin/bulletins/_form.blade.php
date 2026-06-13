<div class="form-group"><label>Título <span class="text-danger">*</span></label><input name="title" class="form-control" required maxlength="255" value="{{ old('title', $bulletin->title ?? '') }}"></div>
<div class="form-group"><label>Slug</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">/boletines/</span></div><input name="slug" class="form-control" value="{{ old('slug', $bulletin->slug ?? '') }}" placeholder="Se genera desde el título"></div></div>
<div class="form-group"><label>Descripción</label><textarea name="description" maxlength="2000" rows="6" class="form-control" placeholder="Describe brevemente el contenido del boletín">{{ old('description', $bulletin->description ?? '') }}</textarea></div>
<div class="form-group">
    <label>Portada</label>
    <select name="cover_image_id" class="form-control">
        <option value="">Sin portada</option>
        @foreach($mediaImages as $image)<option value="{{ $image->id }}" @selected((int) old('cover_image_id', $bulletin->cover_image_id ?? 0) === $image->id)>{{ $image->original_name }}</option>@endforeach
    </select>
    <small class="text-muted">Selecciona una imagen activa de la biblioteca multimedia.</small>
</div>
<div class="form-group mb-0">
    <label>Archivo PDF @if(!isset($bulletin))<span class="text-danger">*</span>@endif</label>
    <div class="custom-file"><input type="file" name="pdf" class="custom-file-input" id="bulletin-pdf" accept="application/pdf" @required(!isset($bulletin))><label class="custom-file-label" for="bulletin-pdf">{{ isset($bulletin) ? 'Reemplazar PDF actual' : 'Seleccionar PDF' }}</label></div>
    <small class="text-muted">Formato PDF, máximo 20 MB.@isset($bulletin) Actual: {{ $bulletin->pdf_original_name }} ({{ $bulletin->formattedSize() }})@endisset</small>
</div>
