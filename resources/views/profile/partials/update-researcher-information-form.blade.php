@php($researcherProfile = $user->researcherProfile)

@if (! $user->hasCompleteResearcherProfile())
    <div class="callout callout-warning">
        <h5><i class="fas fa-exclamation-triangle mr-1"></i> Perfil profesional requerido</h5>
        <p class="mb-0">Debes completar todos los campos y adjuntar tu currículum en PDF antes de continuar usando la plataforma.</p>
    </div>
@endif

<form method="POST" action="{{ route('profile.researcher.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="form-group col-md-6">
            <label for="salutation">Cómo quieres que te llamemos <span class="text-danger">*</span></label>
            <select id="salutation" name="salutation" class="form-control @error('salutation') is-invalid @enderror" required>
                <option value="">Selecciona una opción</option>
                @foreach ($salutations as $salutation)
                    <option value="{{ $salutation }}" @selected(old('salutation', $researcherProfile?->salutation) === $salutation)>{{ $salutation }}</option>
                @endforeach
            </select>
            @error('salutation')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group col-md-6">
            <label for="country">País <span class="text-danger">*</span></label>
            <select id="country" name="country" class="form-control @error('country') is-invalid @enderror" required>
                <option value="">Selecciona un país</option>
                @foreach ($countries as $country)
                    <option value="{{ $country }}" @selected(old('country', $researcherProfile?->country) === $country)>{{ $country }}</option>
                @endforeach
            </select>
            @error('country')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6">
            <label for="academic_title">Título académico <span class="text-danger">*</span></label>
            <input id="academic_title" name="academic_title" type="text" class="form-control @error('academic_title') is-invalid @enderror" value="{{ old('academic_title', $researcherProfile?->academic_title) }}" placeholder="Ej. PhD, MSc, Ingeniero" required>
            @error('academic_title')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group col-md-6">
            <label for="profession">Profesión <span class="text-danger">*</span></label>
            <input id="profession" name="profession" type="text" class="form-control @error('profession') is-invalid @enderror" value="{{ old('profession', $researcherProfile?->profession) }}" required>
            @error('profession')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-group">
        <label for="research_area">Área de investigación <span class="text-danger">*</span></label>
        <select id="research_area" name="research_area" class="form-control @error('research_area') is-invalid @enderror" required>
            <option value="">Selecciona un área</option>
            @foreach ($researchAreas as $researchArea)
                <option value="{{ $researchArea }}" @selected(old('research_area', $researcherProfile?->research_area) === $researchArea)>{{ $researchArea }}</option>
            @endforeach
        </select>
        @error('research_area')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="row">
        <div class="form-group col-md-8">
            <label for="institution">Institución <span class="text-danger">*</span></label>
            <input id="institution" name="institution" type="text" class="form-control @error('institution') is-invalid @enderror" value="{{ old('institution', $researcherProfile?->institution) }}" required>
            @error('institution')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group col-md-4">
            <label for="phone">Teléfono <span class="text-danger">*</span></label>
            <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $researcherProfile?->phone) }}" required>
            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-group">
        <label for="cv">Currículum en PDF @unless($researcherProfile?->cv_path)<span class="text-danger">*</span>@endunless</label>
        <div class="custom-file">
            <input id="cv" name="cv" type="file" class="custom-file-input @error('cv') is-invalid @enderror" accept="application/pdf" {{ $researcherProfile?->cv_path ? '' : 'required' }}>
            <label class="custom-file-label" for="cv">Seleccionar archivo PDF</label>
            @error('cv')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <small class="form-text text-muted">Máximo 5 MB. El archivo se almacena de forma privada.</small>
        @if ($researcherProfile?->cv_path)
            <a href="{{ route('profile.cv.download', $user) }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-file-pdf mr-1"></i> Descargar {{ $researcherProfile->cv_original_name }}
            </a>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Guardar información profesional
    </button>
</form>
