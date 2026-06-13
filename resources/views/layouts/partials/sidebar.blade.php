<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('images/logo_rimis.png') }}" alt="Logo RIMIS" class="rimis-brand-logo">
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <span class="user-initial">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
            </div>
            <div class="info">
                <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
                <small class="text-muted">Usuario conectado</small>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @canany(['dashboard.view', 'dashboard.researcher'])
                    <li class="nav-header">PRINCIPAL</li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcanany

                @can('media.view')
                    <li class="nav-header">GESTIÓN DE CONTENIDO</li>
                    <li class="nav-item {{ request()->routeIs('admin.media-files.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.media-files.*') ? 'active' : '' }}" role="button">
                            <i class="nav-icon fas fa-folder-open"></i>
                            <p>
                                Contenido
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.media-files.index') }}" class="nav-link {{ request()->routeIs('admin.media-files.*') ? 'active' : '' }}">
                                    <i class="far fa-images nav-icon"></i>
                                    <p>Biblioteca multimedia</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                @can('pages.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Páginas</p>
                        </a>
                    </li>
                @endcan

                @can('posts.view')
                    <li class="nav-item {{ request()->routeIs('admin.news.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-newspaper"></i><p>Noticias<i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.index', 'admin.news.edit', 'admin.news.create') ? 'active' : '' }}"><i class="far fa-newspaper nav-icon"></i><p>Listado</p></a></li>
                            <li class="nav-item"><a href="{{ route('admin.news.taxonomies') }}" class="nav-link {{ request()->routeIs('admin.news.taxonomies') ? 'active' : '' }}"><i class="fas fa-tags nav-icon"></i><p>Categorías y etiquetas</p></a></li>
                        </ul>
                    </li>
                @endcan

                @can('bulletins.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.bulletins.index') }}" class="nav-link {{ request()->routeIs('admin.bulletins.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book-open"></i>
                            <p>Boletines</p>
                        </a>
                    </li>
                @endcan

                @can('menus.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.menus.index') }}" class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Menús</p>
                        </a>
                    </li>
                @endcan

                @can('seo.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.seo.edit') }}" class="nav-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-search"></i>
                            <p>SEO global</p>
                        </a>
                    </li>
                @endcan

                @can('users.view')
                    <li class="nav-header">ADMINISTRACIÓN</li>
                    <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" role="button">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>
                                Accesos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="fas fa-users nav-icon"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                @can('profile.view')
                    <li class="nav-header">CUENTA</li>
                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>
                                Mi perfil
                                @if (Auth::user()->hasRole('INVESTIGADOR') && ! Auth::user()->hasCompleteResearcherProfile())
                                    <span class="right badge badge-warning">Pendiente</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>
