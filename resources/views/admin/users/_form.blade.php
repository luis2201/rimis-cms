@php($editingAdministrator = isset($user) && $user->hasRole('ADMINISTRADOR'))

<div class="form-group">
    <label for="name">Nombre completo <span class="text-danger">*</span></label>
    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required autofocus>
    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label for="email">Correo electrónico <span class="text-danger">*</span></label>
    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label for="role">Rol <span class="text-danger">*</span></label>
    @if ($editingAdministrator)
        <input type="text" class="form-control" value="ADMINISTRADOR" disabled>
        <small class="form-text text-muted">El rol administrador está protegido y no puede modificarse desde este módulo.</small>
    @else
        <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
            <option value="">Selecciona un rol</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', isset($user) ? $user->getRoleNames()->first() : '') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
        <small class="form-text text-muted">Los registros públicos reciben automáticamente el rol INVESTIGADOR.</small>
    @endif
</div>

<div class="form-group mb-0">
    <label for="password">
        Contraseña @unless(isset($user))<span class="text-danger">*</span>@endunless
    </label>
    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
    @isset($user)<small class="form-text text-muted">Déjala vacía para conservar la contraseña actual.</small>@endisset
</div>

<div class="form-group mt-3 mb-0">
    <label for="password_confirmation">Confirmar contraseña @unless(isset($user))<span class="text-danger">*</span>@endunless</label>
    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
</div>

@unless($editingAdministrator)
    @php($researcherProfile = $user->researcherProfile ?? null)
    <hr class="my-4">
    <h5><i class="fas fa-user-graduate text-primary mr-2"></i>Información del investigador</h5>
    <p class="text-muted">Estos campos y el currículum son obligatorios cuando el rol seleccionado es INVESTIGADOR.</p>

    <div class="row">
        <div class="form-group col-md-6">
            <label for="salutation">Cómo quiere que lo llamen</label>
            <select id="salutation" name="salutation" class="form-control @error('salutation') is-invalid @enderror">
                <option value="">Selecciona una opción</option>
                @foreach ($salutations as $salutation)
                    <option value="{{ $salutation }}" @selected(old('salutation', $researcherProfile?->salutation) === $salutation)>{{ $salutation }}</option>
                @endforeach
            </select>
            @error('salutation')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="country">País</label>
            <select id="country" name="country" class="form-control @error('country') is-invalid @enderror">
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
            <label for="academic_title">Título académico</label>
            <input id="academic_title" name="academic_title" type="text" class="form-control @error('academic_title') is-invalid @enderror" value="{{ old('academic_title', $researcherProfile?->academic_title) }}">
            @error('academic_title')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="profession">Profesión</label>
            <input id="profession" name="profession" type="text" class="form-control @error('profession') is-invalid @enderror" value="{{ old('profession', $researcherProfile?->profession) }}">
            @error('profession')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-group">
        <label for="research_area">Área de investigación</label>
        <select id="research_area" name="research_area" class="form-control @error('research_area') is-invalid @enderror">
            <option value="">Selecciona un área</option>
            @foreach ($researchAreas as $researchArea)
                <option value="{{ $researchArea }}" @selected(old('research_area', $researcherProfile?->research_area) === $researchArea)>{{ $researchArea }}</option>
            @endforeach
        </select>
        @error('research_area')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="row">
        <div class="form-group col-md-8">
            <label for="institution">Institución</label>
            <input id="institution" name="institution" type="text" class="form-control @error('institution') is-invalid @enderror" value="{{ old('institution', $researcherProfile?->institution) }}">
            @error('institution')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group col-md-4">
            <label for="phone">Teléfono</label>
            <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $researcherProfile?->phone) }}">
            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="cv">Currículum en PDF</label>
        <div class="custom-file">
            <input id="cv" name="cv" type="file" class="custom-file-input @error('cv') is-invalid @enderror" accept="application/pdf">
            <label class="custom-file-label" for="cv">Seleccionar archivo PDF</label>
            @error('cv')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <small class="form-text text-muted">Máximo 5 MB.</small>
        @if (isset($user) && $researcherProfile?->cv_path)
            <a href="{{ route('profile.cv.download', $user) }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-file-pdf mr-1"></i> Descargar currículum actual
            </a>
        @endif
    </div>
@endunless
