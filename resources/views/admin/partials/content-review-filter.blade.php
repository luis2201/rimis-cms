<form class="form-inline mb-2">
    <select name="origin" class="form-control mr-2"><option value="">Todos los orígenes</option><option value="staff" @selected(request('origin')==='staff')>Personal</option><option value="researcher" @selected(request('origin')==='researcher')>Investigador</option></select>
    <select name="review_status" class="form-control mr-2"><option value="">Toda revisión</option>@foreach($model::REVIEW_STATUS_LABELS as $value=>$label)<option value="{{ $value }}" @selected(request('review_status')===$value)>{{ $label }}</option>@endforeach</select>
    <button class="btn btn-outline-primary">Filtrar revisión</button>
</form>
