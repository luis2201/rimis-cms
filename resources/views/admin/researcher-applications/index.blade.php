<x-app-layout>
<x-slot name="header"><h1 class="m-0"><i class="fas fa-user-check text-primary mr-2"></i>Postulaciones a RIMIS</h1></x-slot>
<div class="card card-primary card-outline"><div class="card-header"><form method="GET" class="row align-items-end">
    <div class="col-md-3"><label>Nombre o correo</label><input name="search" value="{{ request('search') }}" class="form-control"></div>
    <div class="col-md-2"><label>Estado</label><select name="status" class="form-control"><option value="">Todos</option>@foreach(\App\Models\ResearcherApplication::STATUS_LABELS as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-2"><label>Institución</label><input name="institution" value="{{ request('institution') }}" class="form-control"></div>
    <div class="col-md-2"><label>Área</label><input name="research_area" value="{{ request('research_area') }}" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-primary">Filtrar</button> <a href="{{ route('admin.researcher-applications.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
</form></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Postulante</th><th>Institución / área</th><th>Estado</th><th>Envío</th><th>Revisor</th><th>Actualización</th><th></th></tr></thead><tbody>
@forelse($applications as $application)<tr><td><strong>{{ $application->user->name }}</strong><div class="small text-muted">{{ $application->user->email }}</div></td><td>{{ $application->user->researcherProfile?->institution ?: '—' }}<div class="small text-muted">{{ $application->user->researcherProfile?->research_area }}</div></td><td><span class="badge badge-info">{{ $application::STATUS_LABELS[$application->status] }}</span></td><td>{{ optional($application->submitted_at)->format('d/m/Y H:i') ?: '—' }}</td><td>{{ $application->reviewer?->name ?: '—' }}</td><td>{{ $application->updated_at->format('d/m/Y H:i') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.researcher-applications.show',$application) }}">Ver</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-5">No hay postulaciones.</td></tr>@endforelse
</tbody></table></div></div>@if($applications->hasPages())<div class="card-footer">{{ $applications->links() }}</div>@endif</div>
</x-app-layout>
