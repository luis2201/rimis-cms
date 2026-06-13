@php
    $suggestions = $seoSuggestions ?? [
        'title' => '',
        'description' => '',
        'keywords' => '',
        'canonical_url' => '',
        'image_url' => null,
    ];
@endphp

<div class="card card-outline card-info mt-4 seo-editor"
    data-suggested-title="{{ $suggestions['title'] }}"
    data-suggested-description="{{ $suggestions['description'] }}"
    data-suggested-keywords="{{ $suggestions['keywords'] }}"
    data-suggested-canonical="{{ $suggestions['canonical_url'] }}"
>
    <div class="card-header"><h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO individual</h3></div>
    <div class="card-body">
        <div class="alert alert-light border">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            Deja un campo vacío para usar la sugerencia del contenido y, como respaldo, la configuración SEO global.
            @isset($seoSuggestions)<button type="button" class="btn btn-sm btn-outline-info ml-2 apply-seo-suggestions">Aplicar sugerencias</button>@endisset
        </div>
        <div class="form-group">
            <label for="seo_title">Meta título <span class="seo-title-count text-muted"></span></label>
            <input id="seo_title" name="seo_title" maxlength="60" class="form-control seo-title-input @error('seo_title') is-invalid @enderror" value="{{ old('seo_title', $page->seo_title ?? '') }}" placeholder="{{ $suggestions['title'] ?: 'Título recomendado, máximo 60 caracteres' }}">
            @error('seo_title')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="seo_description">Meta descripción <span class="seo-description-count text-muted"></span></label>
            <textarea id="seo_description" name="seo_description" maxlength="160" rows="3" class="form-control seo-description-input @error('seo_description') is-invalid @enderror" placeholder="{{ $suggestions['description'] ?: 'Descripción recomendada, máximo 160 caracteres' }}">{{ old('seo_description', $page->seo_description ?? '') }}</textarea>
            @error('seo_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="seo_keywords">Palabras clave</label>
            <input id="seo_keywords" name="seo_keywords" maxlength="500" class="form-control seo-keywords-input" value="{{ old('seo_keywords', $page->seo_keywords ?? '') }}" placeholder="{{ $suggestions['keywords'] ?: 'investigación, ciencia, innovación' }}">
        </div>
        <div class="form-group">
            <label for="seo_canonical_url">URL canónica</label>
            <input id="seo_canonical_url" type="url" name="seo_canonical_url" class="form-control seo-canonical-input @error('seo_canonical_url') is-invalid @enderror" value="{{ old('seo_canonical_url', $page->seo_canonical_url ?? '') }}" placeholder="{{ $suggestions['canonical_url'] ?: 'Se generará desde el slug' }}">
            @error('seo_canonical_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="row">
            <div class="form-group col-md-8">
                <label for="seo_image_id">Imagen social</label>
                <select id="seo_image_id" name="seo_image_id" class="form-control">
                    <option value="">Sugerir desde el contenido o usar imagen global</option>
                    @foreach($mediaImages ?? [] as $image)
                        <option value="{{ $image->id }}" @selected((int) old('seo_image_id', $page->seo_image_id ?? 0) === $image->id)>{{ $image->original_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="seo_index">Visibilidad</label>
                <select id="seo_index" name="seo_index" class="form-control">
                    <option value="1" @selected((string) old('seo_index', isset($page) ? (int) $page->seo_index : 1) === '1')>Indexar</option>
                    <option value="0" @selected((string) old('seo_index', isset($page) ? (int) $page->seo_index : 1) === '0')>No indexar</option>
                </select>
            </div>
        </div>
        <div class="seo-google-preview rounded border p-3 bg-white">
            <small class="text-muted">Vista previa aproximada en Google</small>
            <div class="seo-preview-title mt-2">{{ old('seo_title', $page->seo_title ?? '') ?: ($suggestions['title'] ?: 'Título de la página') }}</div>
            <div class="seo-preview-url">{{ $suggestions['canonical_url'] ?: url('/pagina/slug') }}</div>
            <div class="seo-preview-description">{{ old('seo_description', $page->seo_description ?? '') ?: ($suggestions['description'] ?: 'Descripción de la página para buscadores.') }}</div>
        </div>
    </div>
</div>
