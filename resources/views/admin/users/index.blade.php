<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="m-0"><i class="fas fa-users text-primary mr-2"></i>Usuarios</h1>
                <small class="text-muted">Administra las cuentas y sus roles dentro de RIMIS.</small>
            </div>
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-2 mt-sm-0">
                    <i class="fas fa-user-plus mr-1"></i> Crear usuario
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                    <label for="search" class="small text-muted">Buscar usuario</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input id="search" type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nombre o correo electrónico">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <label for="role" class="small text-muted">Rol</label>
                    <select id="role" name="role" class="form-control">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                    <label for="status" class="small text-muted">Estado</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" @selected(request('status') === 'active')>Activos</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
                    </select>
                </div>
                <div class="col-lg-3 d-flex">
                    <button class="btn btn-primary mr-2"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php($role = $user->getRoleNames()->first())
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="user-list-initial mr-3">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            @if (auth()->user()->is($user))
                                                <span class="badge badge-light border ml-1">Tú</span>
                                            @endif
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $role === 'ADMINISTRADOR' ? 'danger' : ($role === 'WEBMASTER' ? 'primary' : 'info') }} px-2 py-1">
                                        {{ $role ?: 'SIN ROL' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }} px-2 py-1">
                                        <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'ban' }} mr-1"></i>
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-right text-nowrap">
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('users.delete')
                                        @unless (auth()->user()->is($user) || $user->hasRole('ADMINISTRADOR'))
                                            @if ($user->is_active)
                                                <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas desactivar este usuario? No podrá iniciar sesión hasta ser reactivado.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar usuario">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                @can('users.edit')
                                                    <form action="{{ route('admin.users.activate', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activar usuario">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                    <h5>No se encontraron usuarios</h5>
                                    <p class="text-muted mb-0">Prueba modificando los filtros de búsqueda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
