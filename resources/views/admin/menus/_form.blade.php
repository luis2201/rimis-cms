<div class="form-group">
    <label for="name">Nombre <span class="text-danger">*</span></label>
    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $menu->name ?? '') }}" required>
    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
<div class="form-group">
    <label for="location">Ubicación <span class="text-danger">*</span></label>
    <select id="location" name="location" class="form-control @error('location') is-invalid @enderror" required>
        <option value="">Selecciona una ubicación</option>
        @foreach ($locations as $value => $label)
            <option value="{{ $value }}" @selected(old('location', $menu->location ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('location')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
<div class="form-group">
    <label for="description">Descripción</label>
    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $menu->description ?? '') }}</textarea>
    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
<div class="form-group mb-0">
    <label for="is_active">Estado</label>
    <select id="is_active" name="is_active" class="form-control">
        <option value="1" @selected((string) old('is_active', isset($menu) ? (int) $menu->is_active : 1) === '1')>Activo</option>
        <option value="0" @selected((string) old('is_active', isset($menu) ? (int) $menu->is_active : 1) === '0')>Inactivo</option>
    </select>
</div>
