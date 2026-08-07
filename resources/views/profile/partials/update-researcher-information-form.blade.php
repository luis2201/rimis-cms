@php($researcherProfile = $user->researcherProfile)

@if (! $user->hasCompleteResearcherProfile())
    <div class="callout callout-warning">
        <h5><i class="fas fa-exclamation-triangle mr-1"></i> Perfil profesional requerido</h5>
        <p class="mb-0">Debes completar todos los campos y adjuntar tu currículum en PDF antes de continuar usando la plataforma.</p>
    </div>
@endif

<form id="researcher-information-form" method="POST" action="{{ route('profile.researcher.update') }}" enctype="multipart/form-data">
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

    <div class="form-group">
        <label for="research_line">Línea de investigación</label>
        <input id="research_line" name="research_line" class="form-control" value="{{ old('research_line', $researcherProfile?->research_line) }}">
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
        <div id="cv-size-feedback" class="invalid-feedback d-none" role="alert" aria-live="polite"></div>
        @if ($researcherProfile?->cv_path)
            <a href="{{ route('profile.cv.download', $user) }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-file-pdf mr-1"></i> Descargar {{ $researcherProfile->cv_original_name }}
            </a>
        @endif
    </div>

    @if($user->hasRole('INVESTIGADOR'))
    <hr><h4>Perfil público y privacidad</h4>
    @if($researcherProfile && !$researcherProfile->profile_public)<div class="alert alert-warning">Tu perfil público está oculto administrativamente. Puedes actualizar los datos, pero no reactivarlo.</div>@endif
    <div class="form-group"><label for="public_bio">Biografía pública</label><textarea id="public_bio" name="public_bio" rows="5" class="form-control">{{ old('public_bio',$researcherProfile?->public_bio) }}</textarea></div>
    <div class="row">
        @foreach(['orcid'=>'ORCID','google_scholar_url'=>'Google Scholar','researchgate_url'=>'ResearchGate','linkedin_url'=>'LinkedIn','personal_website_url'=>'Sitio web'] as $field=>$label)
        <div class="form-group col-md-6"><label>{{ $label }}</label><input name="{{ $field }}" class="form-control" value="{{ old($field,$researcherProfile?->{$field}) }}">@error($field)<span class="text-danger">{{ $message }}</span>@enderror</div>
        @endforeach
        <div class="form-group col-md-6"><label>Fotografía de la biblioteca</label><select name="profile_photo_id" class="form-control"><option value="">Usar iniciales</option>@foreach($profileImages as $image)<option value="{{ $image->id }}" @selected(old('profile_photo_id',$researcherProfile?->profile_photo_id)==$image->id)>{{ $image->name }}</option>@endforeach</select></div>
    </div>
    <p class="text-muted">Correo, teléfono y currículo permanecen privados salvo que los autorices expresamente.</p>
    <div class="row">
        @foreach(['public_email'=>'Mostrar correo','public_phone'=>'Mostrar teléfono','public_institution'=>'Mostrar institución','public_country'=>'Mostrar país','public_research_area'=>'Mostrar área','public_research_line'=>'Mostrar línea','public_cv'=>'Permitir descarga del CV','publications_section_enabled'=>'Mostrar publicaciones','contributions_section_enabled'=>'Mostrar aportes'] as $field=>$label)
        <div class="col-md-4 form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field,$researcherProfile?->{$field}))><label class="custom-control-label" for="{{ $field }}">{{ $label }}</label></div></div>
        @endforeach
    </div>
    @endif

    <button type="submit" id="researcher-profile-save" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Guardar información profesional
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('researcher-information-form');
        const cvInput = document.getElementById('cv');
        const saveButton = document.getElementById('researcher-profile-save');
        const feedback = document.getElementById('cv-size-feedback');
        const maxBytes = 5 * 1024 * 1024;

        function validateCvFile() {
            if (!cvInput || !saveButton || !feedback) {
                return;
            }

            const file = cvInput.files[0];
            if (!file) {
                feedback.textContent = '';
                feedback.classList.add('d-none');
                cvInput.classList.remove('is-invalid');
                saveButton.disabled = false;
                return;
            }

            if (file.size > maxBytes) {
                feedback.textContent = 'El archivo supera el límite de 5 MB. Selecciona un PDF más pequeño antes de guardar.';
                feedback.classList.remove('d-none');
                cvInput.classList.add('is-invalid');
                saveButton.disabled = true;
            } else {
                feedback.textContent = '';
                feedback.classList.add('d-none');
                cvInput.classList.remove('is-invalid');
                saveButton.disabled = false;
            }
        }

        cvInput?.addEventListener('change', validateCvFile);
        form?.addEventListener('submit', function (event) {
            if (cvInput?.files?.[0]?.size > maxBytes) {
                event.preventDefault();
                validateCvFile();
                cvInput.focus();
            }
        });
    });
</script>
