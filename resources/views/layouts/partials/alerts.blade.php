@php
    $alerts = [
        'success' => ['type' => 'success', 'icon' => 'fas fa-check-circle', 'title' => 'Operación completada'],
        'error' => ['type' => 'danger', 'icon' => 'fas fa-times-circle', 'title' => 'Ocurrió un error'],
        'warning' => ['type' => 'warning', 'icon' => 'fas fa-exclamation-triangle', 'title' => 'Atención'],
        'info' => ['type' => 'info', 'icon' => 'fas fa-info-circle', 'title' => 'Información'],
    ];
@endphp

@foreach ($alerts as $key => $alert)
    @if (session($key))
        <div class="alert alert-{{ $alert['type'] }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="{{ $alert['icon'] }} mr-1"></i> {{ $alert['title'] }}</h5>
            {{ session($key) }}
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="fas fa-exclamation-circle mr-1"></i> Revisa la información ingresada</h5>
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
