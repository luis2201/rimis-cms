<div class="form-group">
    <label for="title">Título <span class="text-danger">*</span></label>
    <input id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title ?? '') }}" required>
    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label for="slug">Slug</label>
    <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text">/pagina/</span></div>
        <input id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug ?? '') }}" placeholder="Se genera desde el título">
        @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-group">
    <label for="excerpt">Extracto</label>
    <textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Resumen breve para presentar la página">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>
    @error('excerpt')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label for="show_title">Presentación pública</label>
    <select id="show_title" name="show_title" class="form-control @error('show_title') is-invalid @enderror">
        <option value="1" @selected((string) old('show_title', isset($page) ? (int) $page->show_title : 1) === '1')>Mostrar título y encabezado de la página</option>
        <option value="0" @selected((string) old('show_title', isset($page) ? (int) $page->show_title : 1) === '0')>Ocultar título y encabezado de la página</option>
    </select>
    @error('show_title')<span class="invalid-feedback">{{ $message }}</span>@enderror
    <small class="form-text text-muted">El título seguirá usándose internamente, en la URL y en la pestaña del navegador.</small>
</div>

<div class="form-group mb-0">
    <label for="content">Contenido introductorio</label>
    <textarea
        id="content"
        name="content"
        rows="18"
        class="form-control page-content-editor @error('content') is-invalid @enderror"
        data-image-list-url="{{ route('admin.media-files.editor.images') }}"
        data-image-upload-url="{{ route('admin.media-files.editor.upload') }}"
    >{{ old('content', $page->content ?? '') }}</textarea>
    @error('content')<span class="invalid-feedback">{{ $message }}</span>@enderror
    <small class="form-text text-muted">Opcional. Usa este editor para una introducción general y el constructor para organizar el resto de la página.</small>
</div>

<div class="modal fade" id="page-media-library-modal" tabindex="-1" role="dialog" aria-labelledby="page-media-library-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="page-media-library-title"><i class="far fa-images mr-2"></i>Biblioteca multimedia RIMIS</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body"><div class="row" id="page-media-library-grid"></div><div class="text-center py-5 d-none" id="page-media-library-empty">No hay imágenes activas disponibles.</div></div>
        </div>
    </div>
</div>

@include('admin.pages._seo-fields')
