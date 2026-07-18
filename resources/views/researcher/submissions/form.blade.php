@php($editing = $model->exists)
@php($titles = ['event'=>'evento','bulletin'=>'boletín','call'=>'convocatoria'])
<x-app-layout>
<x-slot name="header"><h1 class="m-0">{{ $editing ? 'Editar' : 'Proponer' }} {{ $titles[$type] }}</h1></x-slot>
<div class="row"><div class="col-lg-9"><form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('researcher.submissions.'.$type.'.update',$model) : route('researcher.submissions.'.$type.'.store') }}" class="card card-primary card-outline">@csrf @if($editing)@method('PUT')@endif<div class="card-body">
<div class="form-group"><label>Título</label><input id="submission-title" name="title" maxlength="255" class="form-control" value="{{ old('title',$model->title) }}"></div>
<div class="form-group"><label>Slug sugerido por el sistema</label><input id="submission-slug" name="slug" maxlength="255" class="form-control" value="{{ old('slug',$model->slug) }}" placeholder="Se genera automáticamente desde el título"><small class="text-muted">Puedes editar la sugerencia antes de guardar.</small></div>
@if($type !== 'bulletin')<div class="form-group"><label>Resumen</label><textarea name="summary" maxlength="1000" class="form-control">{{ old('summary',$model->summary) }}</textarea></div>@endif
@if($type === 'event')
<div class="row"><div class="col-md-6 form-group"><label>Inicio</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at',$model->starts_at?->format('Y-m-d\TH:i')) }}"></div><div class="col-md-6 form-group"><label>Fin</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at',$model->ends_at?->format('Y-m-d\TH:i')) }}"></div></div>
<div class="form-group"><label>Modalidad</label><select name="modality" class="form-control"><option value="in_person">Presencial</option><option value="virtual" @selected(old('modality',$model->modality)==='virtual')>Virtual</option><option value="hybrid" @selected(old('modality',$model->modality)==='hybrid')>Híbrida</option></select></div>
@foreach(['location'=>'Ubicación o plataforma','organizer'=>'Organizador','responsible_name'=>'Responsable','contact_email'=>'Correo de contacto','contact_phone'=>'Teléfono','website_url'=>'Enlace informativo'] as $field=>$label)<div class="form-group"><label>{{ $label }}</label><input name="{{ $field }}" class="form-control" value="{{ old($field,$model->{$field}) }}"></div>@endforeach
<div class="form-group"><label>Adjunto privado (PDF o imagen, máximo 10 MB)</label><input type="file" name="attachment" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.webp"></div>
@elseif($type === 'bulletin')
<div class="form-group"><label>PDF privado (máximo 10 MB)</label><input type="file" name="pdf" class="form-control-file" accept="application/pdf">@if($model->pdf_original_name)<small>{{ $model->pdf_original_name }}</small>@endif</div>
@else
<div class="row"><div class="col-md-6 form-group"><label>Apertura</label><input type="datetime-local" name="opens_at" class="form-control" value="{{ old('opens_at',$model->opens_at?->format('Y-m-d\TH:i')) }}"></div><div class="col-md-6 form-group"><label>Cierre</label><input type="datetime-local" name="closes_at" class="form-control" value="{{ old('closes_at',$model->closes_at?->format('Y-m-d\TH:i')) }}"></div></div>
<div class="form-group"><label>Bases PDF privadas (máximo 10 MB)</label><input type="file" name="bases_pdf" class="form-control-file" accept="application/pdf"></div><input type="hidden" name="registration_enabled" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="registration_enabled" value="1" @checked(old('registration_enabled',$model->registration_enabled))><label class="form-check-label">Habilitar enlace de inscripción</label></div><div class="form-group"><label>Enlace de inscripción</label><input name="registration_url" type="url" class="form-control" value="{{ old('registration_url',$model->registration_url) }}"></div>
@endif
<div class="form-group"><label>{{ $type === 'bulletin' ? 'Descripción' : 'Descripción completa' }}</label><textarea name="description" rows="10" class="form-control">{{ old('description',$model->description) }}</textarea></div>
</div><div class="card-footer"><button class="btn btn-primary">Guardar borrador</button> <a class="btn btn-secondary" href="{{ route('researcher.submissions.index') }}">Volver</a></div></form></div>
@if($editing)<div class="col-lg-3"><div class="card"><div class="card-body"><p><strong>Revisión:</strong><br>{{ $model->reviewStatusLabel() }}</p>@if($model->review_notes)<div class="alert alert-warning">{{ $model->review_notes }}</div>@endif<form method="POST" action="{{ route('researcher.submissions.'.$type.'.submit',$model) }}">@csrf<button class="btn btn-success btn-block" onclick="return confirm('¿Enviar este aporte a revisión?')">Enviar a revisión</button></form>@if($model->isReviewDraft())<form method="POST" action="{{ route('researcher.submissions.'.$type.'.destroy',$model) }}" class="mt-2">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-block" onclick="return confirm('¿Eliminar este borrador?')">Eliminar</button></form>@endif</div></div></div>@endif</div>
</x-app-layout>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const title = document.getElementById('submission-title');
    const slug = document.getElementById('submission-slug');
    let manuallyEdited = slug.value.trim() !== '';
    const suggestSlug = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    slug.addEventListener('input', () => { manuallyEdited = slug.value.trim() !== ''; });
    title.addEventListener('input', () => { if (! manuallyEdited) slug.value = suggestSlug(title.value); });
    title.addEventListener('blur', () => { if (! slug.value.trim()) { slug.value = suggestSlug(title.value); manuallyEdited = false; } });
});
</script>
