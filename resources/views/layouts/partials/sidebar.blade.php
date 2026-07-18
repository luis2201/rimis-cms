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
                @canany(['dashboard.view', 'dashboard.researcher', 'dashboard.basic'])
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

                @can('events.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.events.index') }}" class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                            <i class="nav-icon far fa-calendar-alt"></i>
                            <p>Eventos</p>
                        </a>
                    </li>
                @endcan

                @can('calls.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.calls.index') }}" class="nav-link {{ request()->routeIs('admin.calls.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bullhorn"></i>
                            <p>Convocatorias</p>
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

                @can('applications.view')
                    <li class="nav-header">MEMBRESÍAS</li>
                    <li class="nav-item"><a href="{{ route('admin.researcher-applications.index') }}" class="nav-link {{ request()->routeIs('admin.researcher-applications.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-check"></i><p>Postulaciones a RIMIS <span class="right badge badge-info">{{ \App\Models\ResearcherApplication::whereIn('status',['submitted','under_review'])->count() }}</span></p></a></li>
                @endcan

                @can('settings.view')
                    @can('researchers.view')
                        <li class="nav-item"><a href="{{ route('admin.researchers.index') }}" class="nav-link {{ request()->routeIs('admin.researchers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-graduate"></i><p>Investigadores</p></a></li>
                    @endcan
                    @can('submissions.view')
                        @php($receivedCount = collect([\App\Models\Event::class, \App\Models\Bulletin::class, \App\Models\CallForProposal::class, \App\Models\ResearchPublication::class])->sum(fn($model) => $model::where('origin','researcher')->whereIn('review_status',['submitted','under_review'])->count()))
                        <li class="nav-item {{ request()->routeIs('admin.submissions.*') ? 'menu-open' : '' }}"><a href="#" class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}"><i class="nav-icon fas fa-inbox"></i><p>Aportes recibidos <span class="badge badge-warning ml-1">{{ $receivedCount }}</span><i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview"><li class="nav-item"><a class="nav-link" href="{{ route('admin.submissions.index') }}">Todos</a></li><li class="nav-item"><a class="nav-link" href="{{ route('admin.submissions.index',['type'=>'event']) }}">Eventos</a></li><li class="nav-item"><a class="nav-link" href="{{ route('admin.submissions.index',['type'=>'bulletin']) }}">Boletines</a></li><li class="nav-item"><a class="nav-link" href="{{ route('admin.submissions.index',['type'=>'call']) }}">Convocatorias</a></li><li class="nav-item"><a class="nav-link" href="{{ route('admin.submissions.index',['type'=>'research_publication']) }}">Investigaciones</a></li></ul></li>
                    @endcan
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.mail.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-envelope-open-text"></i>
                            <p>Configuración de correo</p>
                        </a>
                    </li>
                @endcan

                @can('profile.view')
                    <li class="nav-header">CUENTA</li>
                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>
                                Mi perfil
                                @if (Auth::user()->hasAnyRole(['USUARIO', 'INVESTIGADOR']) && ! Auth::user()->hasCompleteResearcherProfile())
                                    <span class="right badge badge-warning">Pendiente</span>
                                @endif
                            </p>
                        </a>
                    </li>
                @endcan
                @if(Auth::user()->hasAnyRole(['USUARIO','INVESTIGADOR']) && Auth::user()->can('applications.view-own'))
                    <li class="nav-item"><a href="{{ route('applications.show') }}" class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-signature"></i><p>{{ Auth::user()->hasRole('INVESTIGADOR') ? 'Mi membresía RIMIS' : 'Mi postulación' }}</p></a></li>
                @endif
                @if(Auth::user()->hasRole('INVESTIGADOR') && Auth::user()->can('submissions.view-own'))
                    <li class="nav-item {{ request()->routeIs('researcher.submissions.*') ? 'menu-open' : '' }}"><a href="#" class="nav-link {{ request()->routeIs('researcher.submissions.*') ? 'active' : '' }}"><i class="nav-icon fas fa-paper-plane"></i><p>Mis aportes<i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview"><li class="nav-item"><a class="nav-link" href="{{ route('researcher.submissions.index') }}">Todos</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.submissions.index',['type'=>'event']) }}">Eventos</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.submissions.index',['type'=>'bulletin']) }}">Boletines</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.submissions.index',['type'=>'call']) }}">Convocatorias</a></li></ul></li>
                @endif
                @if(Auth::user()->hasRole('INVESTIGADOR') && Auth::user()->can('research-publications.view-own'))
                    <li class="nav-item {{ request()->routeIs('researcher.publications.*') ? 'menu-open' : '' }}"><a href="#" class="nav-link {{ request()->routeIs('researcher.publications.*') ? 'active' : '' }}"><i class="nav-icon fas fa-book"></i><p>Mis publicaciones<i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview"><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index') }}">Todas</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.create') }}">Nueva publicación</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'draft']) }}">Borradores</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'submitted']) }}">Enviadas</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'under_review']) }}">En revisión</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'observed']) }}">Observadas</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'approved']) }}">Aprobadas</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['status'=>'published']) }}">Publicadas</a></li><li class="nav-item"><a class="nav-link" href="{{ route('researcher.publications.index',['review_status'=>'rejected']) }}">Rechazadas</a></li></ul></li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
