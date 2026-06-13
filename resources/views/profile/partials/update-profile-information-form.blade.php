<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <p class="text-muted">Actualiza el nombre y correo electrónico asociados a tu cuenta.</p>

    <div class="form-group">
        <label for="name"><i class="fas fa-user mr-1 text-muted"></i> Nombre</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="email"><i class="fas fa-envelope mr-1 text-muted"></i> Correo electrónico</label>
        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="callout callout-warning mt-3 mb-0">
                <p class="mb-2">Tu correo electrónico no está verificado.</p>
                <button form="send-verification" class="btn btn-sm btn-warning">
                    <i class="fas fa-paper-plane mr-1"></i> Reenviar verificación
                </button>
            </div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Guardar cambios
    </button>

    @if (session('status') === 'profile-updated')
        <span class="text-success ml-2"><i class="fas fa-check-circle mr-1"></i>Guardado</span>
    @endif
</form>
