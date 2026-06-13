<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Alternar menú">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i class="fas fa-home mr-1"></i> Inicio
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" target="_blank" class="nav-link">
                <i class="fas fa-external-link-alt mr-1"></i> Ver sitio
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="user-initial mr-2">
                    {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                <span class="user-menu-caret ml-2" aria-hidden="true"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <div class="dropdown-item">
                    <div class="media">
                        <span class="user-initial mr-3">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                        <div class="media-body">
                            <h3 class="dropdown-item-title">{{ Auth::user()->name }}</h3>
                            <p class="text-sm text-muted mb-0">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                @can('profile.view')
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user-cog mr-2 text-muted"></i> Mi perfil
                    </a>
                    <div class="dropdown-divider"></div>
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
