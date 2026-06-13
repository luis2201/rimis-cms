<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-search text-primary mr-2"></i>SEO global</h1><small class="text-muted">Valores de respaldo para contenidos sin configuración específica.</small></x-slot>
    <form method="POST" action="{{ route('admin.seo.update') }}" class="row">
        @csrf @method('PUT')
        <div class="col-lg-8">
            <div class="card card-primary card-outline shadow-sm seo-editor"
                data-suggested-title="{{ $suggestions['title'] }}"
                data-suggested-description="{{ $suggestions['description'] }}"
                data-suggested-keywords="{{ $suggestions['keywords'] }}"
                data-suggested-canonical="{{ $suggestions['canonical_url'] }}"
            >
                <div class="card-body">
                    <div class="alert alert-light border"><i class="fas fa-lightbulb text-warning mr-1"></i> Sugerencias generadas desde el nombre, descripción y eslogan del sitio.<button type="button" class="btn btn-sm btn-outline-info ml-2 apply-seo-suggestions">Aplicar sugerencias</button></div>
                    <div class="form-group"><label>Meta título <span class="seo-title-count text-muted"></span></label><input name="meta_title" maxlength="60" class="form-control seo-title-input" value="{{ old('meta_title', $settings->meta_title) }}" placeholder="{{ $suggestions['title'] }}"><small class="text-muted">Máximo recomendado: 60 caracteres.</small></div>
                    <div class="form-group"><label>Meta descripción <span class="seo-description-count text-muted"></span></label><textarea name="meta_description" maxlength="160" rows="3" class="form-control seo-description-input" placeholder="{{ $suggestions['description'] }}">{{ old('meta_description', $settings->meta_description) }}</textarea><small class="text-muted">Máximo recomendado: 160 caracteres.</small></div>
                    <div class="form-group"><label>Palabras clave</label><input name="meta_keywords" maxlength="500" class="form-control seo-keywords-input" value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="{{ $suggestions['keywords'] }}"></div>
                    <input type="hidden" class="seo-canonical-input" value="{{ $suggestions['canonical_url'] }}">
                    <div class="form-group"><label>Imagen social global</label><select name="og_image" class="form-control"><option value="">Sin imagen global</option>@foreach($mediaImages as $image)<option value="{{ $image->publicUrl() }}" @selected($settings->og_image === $image->publicUrl())>{{ $image->original_name }}</option>@endforeach</select></div>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Indexación global</label><select name="seo_index" class="form-control"><option value="1" @selected($settings->seo_index)>Permitir indexación</option><option value="0" @selected(!$settings->seo_index)>Bloquear indexación</option></select></div>
                        <div class="form-group col-md-6"><label>Twitter Card</label><select name="twitter_card" class="form-control"><option value="summary_large_image" @selected($settings->twitter_card === 'summary_large_image')>Imagen grande</option><option value="summary" @selected($settings->twitter_card === 'summary')>Resumen</option></select></div>
                    </div>
                    <div class="seo-google-preview rounded border p-3 bg-white"><small class="text-muted">Vista previa aproximada en Google</small><div class="seo-preview-title mt-2">{{ $preview['title'] }}</div><div class="seo-preview-url">{{ $suggestions['canonical_url'] }}</div><div class="seo-preview-description">{{ $preview['description'] }}</div></div>
                </div>
                <div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar SEO global</button></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-info"><div class="card-header"><h3 class="card-title">Herramientas</h3></div><div class="card-body">
                <a href="{{ route('seo.sitemap') }}" target="_blank" class="btn btn-outline-primary btn-block"><i class="fas fa-sitemap mr-1"></i> Ver sitemap.xml</a>
                <a href="{{ route('seo.robots') }}" target="_blank" class="btn btn-outline-secondary btn-block"><i class="fas fa-robot mr-1"></i> Ver robots.txt</a>
                <hr><small class="text-muted">El SEO individual de cada página tiene prioridad. Los campos vacíos utilizan sugerencias y estos valores globales como respaldo.</small>
            </div></div>
        </div>
    </form>
</x-app-layout>
