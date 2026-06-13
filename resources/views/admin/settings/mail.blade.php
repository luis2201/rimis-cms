<x-app-layout>
    <x-slot name="header"><h1 class="m-0"><i class="fas fa-envelope-open-text text-primary mr-2"></i>Configuración de correo</h1><small class="text-muted">Define la cuenta que enviará notificaciones y correos del sistema.</small></x-slot>
    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="card card-primary card-outline shadow-sm">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="custom-control custom-checkbox mb-4">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1" @checked(old('enabled', $settings?->enabled ?? false)) @cannot('settings.edit') disabled @endcannot>
                        <label class="custom-control-label" for="enabled">Usar esta configuración SMTP para los correos del sistema</label>
                    </div>
                    <h5 class="border-bottom pb-2"><i class="fas fa-server text-primary mr-2"></i>Servidor SMTP</h5>
                    <div class="row">
                        <div class="form-group col-md-8"><label>Servidor <span class="text-danger">*</span></label><input name="host" class="form-control" required maxlength="255" value="{{ old('host', $settings?->host) }}" placeholder="smtp.gmail.com" @cannot('settings.edit') disabled @endcannot></div>
                        <div class="form-group col-md-4"><label>Puerto <span class="text-danger">*</span></label><input type="number" name="port" class="form-control" required min="1" max="65535" value="{{ old('port', $settings?->port ?? 587) }}" @cannot('settings.edit') disabled @endcannot></div>
                        <div class="form-group col-md-6"><label>Cifrado</label><select name="encryption" class="form-control" @cannot('settings.edit') disabled @endcannot><option value="">Sin cifrado</option><option value="ssl" @selected(old('encryption', $settings?->encryption) === 'ssl')>SSL</option><option value="tls" @selected(old('encryption', $settings?->encryption) === 'tls')>TLS</option></select></div>
                        <div class="form-group col-md-6"><label>Usuario <span class="text-danger">*</span></label><input name="username" class="form-control" required maxlength="255" autocomplete="off" value="{{ old('username', $settings?->username) }}" @cannot('settings.edit') disabled @endcannot></div>
                    </div>
                    <div class="form-group"><label>Contraseña de aplicación @unless($settings)<span class="text-danger">*</span>@endunless</label><input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="{{ $settings ? 'Dejar vacío para conservar la contraseña actual' : '' }}" @required(!$settings) @cannot('settings.edit') disabled @endcannot><small class="text-muted">Se almacena cifrada y no volverá a mostrarse.</small></div>
                    <h5 class="border-bottom pb-2 mt-4"><i class="fas fa-paper-plane text-primary mr-2"></i>Remitente</h5>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Correo remitente <span class="text-danger">*</span></label><input type="email" name="from_address" class="form-control" required maxlength="255" value="{{ old('from_address', $settings?->from_address) }}" @cannot('settings.edit') disabled @endcannot></div>
                        <div class="form-group col-md-6"><label>Nombre remitente <span class="text-danger">*</span></label><input name="from_name" class="form-control" required maxlength="255" value="{{ old('from_name', $settings?->from_name ?? 'RIMIS') }}" @cannot('settings.edit') disabled @endcannot></div>
                    </div>
                </div>
                @can('settings.edit')<div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar configuración</button></div>@endcan
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Correo de prueba</h3></div>
                <form method="POST" action="{{ route('admin.settings.mail.test') }}">
                    @csrf
                    <div class="card-body"><p class="text-muted small">Envía un mensaje para comprobar la conexión y el remitente configurado.</p><div class="form-group mb-0"><label>Destinatario</label><input type="email" name="test_email" class="form-control" required value="{{ old('test_email', auth()->user()->email) }}" @cannot('settings.edit') disabled @endcannot></div></div>
                    @can('settings.edit')<div class="card-footer"><button class="btn btn-outline-info btn-block"><i class="fas fa-paper-plane mr-1"></i> Enviar prueba</button></div>@endcan
                </form>
            </div>
            <div class="alert alert-warning"><i class="fas fa-shield-alt mr-1"></i> Para Gmail utiliza una contraseña de aplicación, no la contraseña principal de la cuenta.</div>
        </div>
    </div>
</x-app-layout>
