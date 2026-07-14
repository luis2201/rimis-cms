<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0"><i class="fas fa-user-cog text-primary mr-2"></i>Mi perfil</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            @if ($user->hasAnyRole(['USUARIO', 'INVESTIGADOR']))
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Información profesional</h3>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-researcher-information-form')
                    </div>
                </div>
            @endif

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Información personal</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key mr-2"></i>Seguridad</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @unless ($user->hasRole('ADMINISTRADOR'))
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Desactivar cuenta</h3>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endunless
        </div>

        <div class="col-lg-4">
            <div class="card card-widget widget-user">
                <div class="widget-user-header bg-info">
                    <h3 class="widget-user-username">{{ $user->name }}</h3>
                    <h5 class="widget-user-desc">{{ $user->getRoleNames()->first() ?: 'Usuario de RIMIS' }}</h5>
                </div>
                <div class="widget-user-image">
                    <span class="img-circle elevation-2 bg-white text-info d-flex align-items-center justify-content-center font-weight-bold" style="width: 90px; height: 90px; font-size: 2rem;">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <div class="card-footer">
                    <div class="text-center text-muted pt-3">
                        <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
                    </div>
                    @if ($user->hasAnyRole(['USUARIO', 'INVESTIGADOR']))
                        <div class="text-center mt-3">
                            <span class="badge badge-{{ $user->hasCompleteResearcherProfile() ? 'success' : 'warning' }}">
                                <i class="fas fa-{{ $user->hasCompleteResearcherProfile() ? 'check-circle' : 'exclamation-circle' }} mr-1"></i>
                                Perfil {{ $user->hasCompleteResearcherProfile() ? 'completo' : 'pendiente' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
