<x-app-layout>
    <x-slot name="header">
        <div><h1 class="m-0"><i class="fas fa-user-shield text-primary mr-2"></i>Roles y permisos</h1><small class="text-muted">Consulta los accesos asignados a cada perfil del sistema.</small></div>
    </x-slot>

    <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>El rol <strong>ADMINISTRADOR</strong> conserva siempre acceso total. Los demás roles pueden configurarse individualmente.</div>
    <div class="card card-primary card-outline shadow-sm"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0"><thead class="thead-light"><tr><th>Rol</th><th>Usuarios</th><th>Permisos</th><th>Configuración</th><th class="text-right">Acciones</th></tr></thead><tbody>
        @forelse($roles as $role)
            <tr><td><strong>{{ $role->name }}</strong></td><td><span class="badge badge-light border px-2 py-1">{{ $role->users_count }}</span></td><td><span class="badge badge-info px-2 py-1">{{ $role->permissions_count }}</span></td><td>@if($role->name==='ADMINISTRADOR')<span class="text-muted"><i class="fas fa-lock mr-1"></i>Protegido</span>@else<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Editable</span>@endif</td><td class="text-right">@can('roles.edit')<a href="{{ route('admin.roles.edit',$role) }}" class="btn btn-sm btn-outline-primary" title="{{ $role->name==='ADMINISTRADOR'?'Consultar permisos':'Asignar permisos' }}"><i class="fas fa-{{ $role->name==='ADMINISTRADOR'?'eye':'user-cog' }} mr-1"></i>{{ $role->name==='ADMINISTRADOR'?'Consultar':'Configurar' }}</a>@endcan</td></tr>
        @empty<tr><td colspan="5" class="text-center py-5">No existen roles configurados.</td></tr>@endforelse
        </tbody></table>
    </div></div></div>
</x-app-layout>
