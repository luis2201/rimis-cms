<p class="text-muted">
    Al desactivar tu cuenta conservarás tus datos, perfil profesional y currículum, pero no podrás iniciar sesión hasta que un administrador reactive tu acceso.
</p>

<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-user-deletion">
    <i class="fas fa-user-slash mr-1"></i> Desactivar cuenta
</button>

<div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog" aria-labelledby="confirm-user-deletion-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h5 class="modal-title" id="confirm-user-deletion-title">
                    <i class="fas fa-exclamation-triangle text-danger mr-1"></i> Confirmar desactivación
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>Ingresa tu contraseña para confirmar que deseas desactivar tu cuenta.</p>
                <div class="form-group mb-0">
                    <label for="delete_password">Contraseña</label>
                    <input id="delete_password" name="password" type="password" class="form-control {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}">
                    @if ($errors->userDeletion->has('password'))
                        <span class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</span>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-user-slash mr-1"></i> Desactivar cuenta
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.jQuery('#confirm-user-deletion').modal('show');
        });
    </script>
@endif
