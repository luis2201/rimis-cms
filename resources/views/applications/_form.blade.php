@csrf
@if(isset($application)) @method('PUT') @endif
@foreach(['motivation' => '¿Por qué deseas pertenecer a RIMIS?', 'experience_summary' => 'Resumen de experiencia', 'expected_contribution' => 'Contribución esperada'] as $field => $label)
    <div class="form-group">
        <label for="{{ $field }}">{{ $label }}</label>
        <textarea id="{{ $field }}" name="{{ $field }}" rows="6" maxlength="5000" class="form-control @error($field) is-invalid @enderror">{{ old($field, $application->{$field} ?? '') }}</textarea>
        <small class="form-text text-muted">Máximo 5000 caracteres.</small>
        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endforeach
<button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar borrador</button>
