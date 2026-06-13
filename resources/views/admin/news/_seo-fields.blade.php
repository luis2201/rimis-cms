@php
    $suggestions = $seoSuggestions ?? [
        'title' => '',
        'description' => '',
        'keywords' => '',
        'canonical_url' => '',
        'image_url' => null,
    ];
@endphp

<div class="card card-outline card-info seo-editor"
    data-suggested-title="{{ $suggestions['title'] }}"
    data-suggested-description="{{ $suggestions['description'] }}"
    data-suggested-keywords="{{ $suggestions['keywords'] }}"
    data-suggested-canonical="{{ $suggestions['canonical_url'] }}"
>
    <div class="card-header"><h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO individual</h3></div>
    <div class="card-body">
        <div class="alert alert-light border">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            Sugerencias generadas desde el título, extracto, contenido, categoría y etiquetas.
            @isset($seoSuggestions)<button type="button" class="btn btn-sm btn-outline-info ml-2 apply-seo-suggestions">Aplicar sugerencias</button>@endisset
        </div>
        <div class="form-group">
            <label for="seo_title">Meta título <span class="seo-title-count text-muted"></span></label>
            <input id="seo_title" name="seo_title" maxlength="60" class="form-control seo-title-input" value="{{ old('seo_title', $news->seo_title ?? '') }}" placeholder="{{ $suggestions['title'] ?: 'Título recomendado, máximo 60 caracteres' }}">
        </div>
        <div class="form-group">
            <label for="seo_description">Meta descripción <span class="seo-description-count text-muted"></span></label>
            <textarea id="seo_description" name="seo_description" maxlength="160" rows="3" class="form-control seo-description-input" placeholder="{{ $suggestions['description'] ?: 'Descripción recomendada, máximo 160 caracteres' }}">{{ old('seo_description', $news->seo_description ?? '') }}</textarea>
        </div>
        <div class="form-group">
            <label for="seo_keywords">Palabras clave</label>
            <input id="seo_keywords" name="seo_keywords" maxlength="500" class="form-control seo-keywords-input" value="{{ old('seo_keywords', $news->seo_keywords ?? '') }}" placeholder="{{ $suggestions['keywords'] ?: 'investigación, ciencia, innovación' }}">
        </div>
        <input type="hidden" class="seo-canonical-input" value="">
        <div class="form-group">
            <label for="seo_index">Visibilidad</label>
            <select id="seo_index" name="seo_index" class="form-control"><option value="1" @selected((string) old('seo_index', isset($news) ? (int) $news->seo_index : 1) === '1')>Indexar</option><option value="0" @selected((string) old('seo_index', isset($news) ? (int) $news->seo_index : 1) === '0')>No indexar</option></select>
        </div>
        <div class="seo-google-preview rounded border bg-white p-3">
            <small class="text-muted">Vista previa aproximada en Google</small>
            <div class="seo-preview-title mt-2">{{ old('seo_title', $news->seo_title ?? '') ?: ($suggestions['title'] ?: 'Título de la noticia') }}</div>
            <div class="seo-preview-url">{{ $suggestions['canonical_url'] ?: url('/noticias/slug') }}</div>
            <div class="seo-preview-description">{{ old('seo_description', $news->seo_description ?? '') ?: ($suggestions['description'] ?: 'Descripción de la noticia para buscadores.') }}</div>
        </div>
    </div>
</div>
