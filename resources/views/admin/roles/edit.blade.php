<x-app-layout>
    <x-slot name="header"><div><h1 class="m-0"><i class="fas fa-key text-primary mr-2"></i>Permisos de {{ $role->name }}</h1><small class="text-muted">Define las acciones disponibles para los usuarios que tienen este rol.</small></div></x-slot>
    @php
        $protected = $role->name === 'ADMINISTRADOR';
        $selected = old('permissions', $role->permissions->pluck('name')->all());
        $moduleLabels = ['dashboard'=>'Panel','users'=>'Usuarios','roles'=>'Roles y permisos','settings'=>'Configuración','media'=>'Archivos multimedia','menus'=>'Menús','pages'=>'Páginas','posts'=>'Noticias','events'=>'Eventos','calls'=>'Convocatorias','bulletins'=>'Boletines','journals'=>'Revistas','articles'=>'Artículos','researchers'=>'Investigadores','notifications'=>'Notificaciones','seo'=>'SEO','profile'=>'Perfil','research'=>'Investigación','applications'=>'Postulaciones','submissions'=>'Aportes','research-publications'=>'Publicaciones científicas','researcher-profile'=>'Perfil profesional','subscriptions'=>'Suscripciones'];
        $actionLabels = ['view'=>'Ver','view-own'=>'Ver propios','create'=>'Crear','edit'=>'Editar','edit-own'=>'Editar propios','delete'=>'Eliminar','delete-own'=>'Eliminar propios','publish'=>'Publicar','unpublish'=>'Despublicar','submit'=>'Enviar','download-own'=>'Descargar propios','review'=>'Revisar','observe'=>'Observar','approve'=>'Aprobar','reject'=>'Rechazar','send'=>'Enviar','manage-privacy'=>'Gestionar privacidad','manage-visibility'=>'Gestionar visibilidad','basic'=>'Panel básico','researcher'=>'Panel de miembro','withdraw'=>'Retirar'];
    @endphp
    @if($protected)<div class="alert alert-warning"><i class="fas fa-lock mr-2"></i>Este rol es de solo lectura y recibe automáticamente todos los permisos disponibles.</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Revisa la selección.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT')
        @unless($protected)<div class="card shadow-sm"><div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-center"><span class="text-muted"><span id="permission-count">{{ count($selected) }}</span> permisos seleccionados</span><div><button type="button" id="select-all" class="btn btn-sm btn-outline-primary mr-1">Seleccionar todos</button><button type="button" id="clear-all" class="btn btn-sm btn-outline-secondary">Limpiar</button></div></div></div>@endunless
        <div class="row">
            @foreach($permissionGroups as $module=>$permissions)
                <div class="col-lg-4 col-md-6"><div class="card card-outline card-secondary h-100 mb-3"><div class="card-header"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input module-toggle" id="module_{{ $loop->index }}" data-module="group_{{ $loop->index }}" @disabled($protected)><label class="custom-control-label font-weight-bold" for="module_{{ $loop->index }}">{{ $moduleLabels[$module] ?? str($module)->headline() }}</label></div></div><div class="card-body py-2 permission-group" id="group_{{ $loop->index }}">
                    @foreach($permissions as $permission)@php($action=str($permission->name)->after('.')->toString())<div class="custom-control custom-checkbox my-2"><input type="checkbox" class="custom-control-input permission-checkbox" id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name,$selected,true)) @disabled($protected)><label class="custom-control-label" for="permission_{{ $permission->id }}">{{ $actionLabels[$action] ?? str($action)->headline() }}<small class="d-block text-muted">{{ $permission->name }}</small></label></div>@endforeach
                </div></div></div>
            @endforeach
        </div>
        <div class="card"><div class="card-body d-flex justify-content-between"><a href="{{ route('admin.roles.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Volver</a>@unless($protected)<button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Guardar permisos</button>@endunless</div></div>
    </form>
    @unless($protected)
    <script>
    document.addEventListener('DOMContentLoaded',function(){const permissions=[...document.querySelectorAll('.permission-checkbox')],count=document.getElementById('permission-count');const update=()=>{count.textContent=permissions.filter(item=>item.checked).length;document.querySelectorAll('.module-toggle').forEach(toggle=>{const items=[...document.querySelectorAll('#'+toggle.dataset.module+' .permission-checkbox')];toggle.checked=items.length>0&&items.every(item=>item.checked);toggle.indeterminate=items.some(item=>item.checked)&&!toggle.checked;});};permissions.forEach(item=>item.addEventListener('change',update));document.querySelectorAll('.module-toggle').forEach(toggle=>toggle.addEventListener('change',()=>{document.querySelectorAll('#'+toggle.dataset.module+' .permission-checkbox').forEach(item=>item.checked=toggle.checked);update();}));document.getElementById('select-all').addEventListener('click',()=>{permissions.forEach(item=>item.checked=true);update();});document.getElementById('clear-all').addEventListener('click',()=>{permissions.forEach(item=>item.checked=false);update();});update();});
    </script>
    @endunless
</x-app-layout>
