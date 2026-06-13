@php
    $data = $block->data ?? [];
    $itemsText = collect($data['items'] ?? [])->map(fn ($item) => implode(' | ', $item))->implode("\n");
@endphp

<div class="form-group">
    <label>Tipo de bloque <span class="text-danger">*</span></label>
    <select name="type" class="form-control page-block-type" required>
        @foreach(\App\Models\PageBlock::TYPES as $value => $definition)
            <option value="{{ $value }}" @selected(($block->type ?? '') === $value)>{{ $definition['label'] }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Nombre interno</label>
    <input name="name" class="form-control" value="{{ $block->name ?? '' }}" maxlength="120" placeholder="Solo visible en administración">
</div>

<div class="page-block-field" data-block-types="hero,text,text_image,gallery,cards,video,buttons,faq,dynamic_list">
    <div class="form-group"><label>Título</label><input name="title" class="form-control" value="{{ $data['title'] ?? '' }}" maxlength="255"></div>
</div>
<div class="page-block-field" data-block-types="hero">
    <div class="form-group"><label>Subtítulo</label><textarea name="subtitle" rows="2" class="form-control" maxlength="500">{{ $data['subtitle'] ?? '' }}</textarea></div>
</div>
<div class="page-block-field" data-block-types="text,text_image,html">
    <div class="form-group">
        <label>{{ ($block->type ?? '') === 'html' ? 'HTML personalizado' : 'Contenido' }}</label>
        <textarea name="content" rows="7" class="form-control" placeholder="<p>Contenido del bloque...</p>">{{ $data['content'] ?? '' }}</textarea>
        <small class="text-muted">Admite HTML seguro; scripts y atributos inseguros serán eliminados.</small>
    </div>
</div>
<div class="page-block-field" data-block-types="hero,image,text_image">
    <div class="form-group">
        <label>Imagen de la biblioteca</label>
        <select name="image_id" class="form-control">
            <option value="">Sin imagen</option>
            @foreach($mediaImages as $image)
                <option value="{{ $image->id }}" @selected((int) ($data['image_id'] ?? 0) === $image->id)>{{ $image->original_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="page-block-field" data-block-types="text_image">
    <div class="form-group">
        <label>Posición de imagen</label>
        <select name="image_position" class="form-control">
            <option value="right" @selected(($data['image_position'] ?? 'right') === 'right')>Derecha</option>
            <option value="left" @selected(($data['image_position'] ?? '') === 'left')>Izquierda</option>
        </select>
    </div>
</div>
<div class="page-block-field" data-block-types="gallery">
    <div class="form-group">
        <label>Imágenes de la galería</label>
        <select name="image_ids[]" class="form-control page-block-gallery-select" multiple size="7">
            @foreach($mediaImages as $image)
                <option value="{{ $image->id }}" @selected(in_array($image->id, $data['image_ids'] ?? []))>{{ $image->original_name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Usa Ctrl o Cmd para seleccionar varias imágenes.</small>
    </div>
</div>
<div class="page-block-field" data-block-types="video">
    <div class="form-group"><label>URL del video</label><input name="url" class="form-control" value="{{ $data['url'] ?? '' }}" placeholder="https://www.youtube.com/watch?v=..."></div>
</div>
<div class="page-block-field" data-block-types="cards,buttons,faq">
    <div class="form-group">
        <label>Elementos, uno por línea</label>
        <textarea name="items" rows="7" class="form-control" placeholder="Título | Descripción | URL">{{ $itemsText }}</textarea>
        <small class="text-muted block-items-help">Cards: título | descripción | URL. Botones: etiqueta | URL | primary/outline. FAQ: pregunta | respuesta.</small>
    </div>
</div>
<div class="page-block-field" data-block-types="dynamic_list">
    <div class="row">
        <div class="form-group col-md-8">
            <label>Fuente</label>
            <select name="source" class="form-control">
                <option value="published_pages" @selected(($data['source'] ?? '') === 'published_pages')>Páginas publicadas</option>
                <option value="media_images" @selected(($data['source'] ?? '') === 'media_images')>Imágenes multimedia activas</option>
                <option value="recent_news" @selected(($data['source'] ?? '') === 'recent_news')>Noticias recientes</option>
                <option value="featured_news" @selected(($data['source'] ?? '') === 'featured_news')>Noticias destacadas</option>
                <option value="news_category" @selected(($data['source'] ?? '') === 'news_category')>Noticias de una categoría</option>
            </select>
        </div>
        <div class="form-group col-md-4"><label>Límite</label><input type="number" name="limit" class="form-control" min="1" max="24" value="{{ $data['limit'] ?? 6 }}"></div>
    </div>
    <div class="form-group">
        <label>Categoría de noticias</label>
        <select name="category_id" class="form-control">
            <option value="">Selecciona una categoría cuando la fuente lo requiera</option>
            @foreach($newsCategories ?? collect() as $category)
                <option value="{{ $category->id }}" @selected((int) ($data['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group mb-0">
    <label>Estado</label>
    <select name="is_active" class="form-control"><option value="1" @selected($block->is_active ?? true)>Activo</option><option value="0" @selected(isset($block) && !$block->is_active)>Inactivo</option></select>
</div>
