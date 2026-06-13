<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <p class="text-muted">Utiliza una contraseña larga y difícil de adivinar para proteger tu cuenta.</p>

    <div class="form-group">
        <label for="current_password">Contraseña actual</label>
        <input id="current_password" name="current_password" type="password" class="form-control {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}" autocomplete="current-password">
        @if ($errors->updatePassword->has('current_password'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</span>
        @endif
    </div>

    <div class="form-group">
        <label for="password">Nueva contraseña</label>
        <input id="password" name="password" type="password" class="form-control {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password">
        @if ($errors->updatePassword->has('password'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</span>
        @endif
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}" autocomplete="new-password">
        @if ($errors->updatePassword->has('password_confirmation'))
            <span class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</span>
        @endif
    </div>

    <button type="submit" class="btn btn-warning">
        <i class="fas fa-key mr-1"></i> Actualizar contraseña
    </button>

    @if (session('status') === 'password-updated')
        <span class="text-success ml-2"><i class="fas fa-check-circle mr-1"></i>Actualizada</span>
    @endif
</form>
