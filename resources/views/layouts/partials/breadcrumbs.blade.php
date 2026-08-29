@php
    $breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'fas fa-home']];

    if (request()->routeIs('admin.media-files.*')) {
        $breadcrumbs[] = [
            'label' => 'Biblioteca multimedia',
            'url' => request()->routeIs('admin.media-files.index') ? null : route('admin.media-files.index'),
        ];

        $action = match (request()->route()->getName()) {
            'admin.media-files.create' => 'Subir archivo',
            'admin.media-files.show' => 'Detalle',
            'admin.media-files.edit' => 'Editar archivo',
            default => null,
        };

        if ($action) {
            $breadcrumbs[] = ['label' => $action, 'url' => null];
        }
    } elseif (request()->routeIs('admin.news.*')) {
        $breadcrumbs[] = ['label' => 'Noticias', 'url' => request()->routeIs('admin.news.index') ? null : route('admin.news.index')];
        $action = match (request()->route()->getName()) {
            'admin.news.create' => 'Crear noticia',
            'admin.news.edit' => 'Editar noticia',
            'admin.news.taxonomies' => 'Categorías y etiquetas',
            default => null,
        };
        if ($action) $breadcrumbs[] = ['label' => $action, 'url' => null];
    } elseif (request()->routeIs('admin.bulletins.*')) {
        $breadcrumbs[] = ['label' => 'Boletines', 'url' => request()->routeIs('admin.bulletins.index') ? null : route('admin.bulletins.index')];
        $action = match (request()->route()->getName()) {
            'admin.bulletins.create' => 'Crear boletín',
            'admin.bulletins.edit' => 'Editar boletín',
            default => null,
        };
        if ($action) $breadcrumbs[] = ['label' => $action, 'url' => null];
    } elseif (request()->routeIs('admin.pages.*')) {
        $breadcrumbs[] = [
            'label' => 'Páginas',
            'url' => request()->routeIs('admin.pages.index') ? null : route('admin.pages.index'),
        ];

        $action = match (request()->route()->getName()) {
            'admin.pages.create' => 'Crear página',
            'admin.pages.edit' => 'Editar página',
            default => null,
        };

        if ($action) {
            $breadcrumbs[] = ['label' => $action, 'url' => null];
        }
    } elseif (request()->routeIs('admin.seo.*')) {
        $breadcrumbs[] = ['label' => 'SEO global', 'url' => null];
    } elseif (request()->routeIs('admin.menus.*')) {
        $breadcrumbs[] = [
            'label' => 'Menús',
            'url' => request()->routeIs('admin.menus.index') ? null : route('admin.menus.index'),
        ];

        $action = match (request()->route()->getName()) {
            'admin.menus.create' => 'Crear menú',
            'admin.menus.edit' => 'Editar menú',
            'admin.menus.show' => 'Administrar ítems',
            default => null,
        };

        if ($action) {
            $breadcrumbs[] = ['label' => $action, 'url' => null];
        }
    } elseif (request()->routeIs('admin.roles.*')) {
        $breadcrumbs[] = [
            'label' => 'Roles y permisos',
            'url' => request()->routeIs('admin.roles.index') ? null : route('admin.roles.index'),
        ];

        if (request()->routeIs('admin.roles.edit')) {
            $breadcrumbs[] = ['label' => 'Asignar permisos', 'url' => null];
        }
    } elseif (request()->routeIs('admin.users.*')) {
        $breadcrumbs[] = [
            'label' => 'Usuarios',
            'url' => request()->routeIs('admin.users.index') ? null : route('admin.users.index'),
        ];

        $action = match (request()->route()->getName()) {
            'admin.users.create' => 'Crear usuario',
            'admin.users.edit' => 'Editar usuario',
            default => null,
        };

        if ($action) {
            $breadcrumbs[] = ['label' => $action, 'url' => null];
        }
    } elseif (request()->routeIs('profile.*')) {
        $breadcrumbs[] = ['label' => 'Mi perfil', 'url' => null];
    }
@endphp

<ol class="breadcrumb float-sm-right mb-0">
    @foreach ($breadcrumbs as $breadcrumb)
        <li class="breadcrumb-item {{ $breadcrumb['url'] ? '' : 'active' }}">
            @if ($breadcrumb['url'])
                <a href="{{ $breadcrumb['url'] }}">
                    @isset($breadcrumb['icon'])<i class="{{ $breadcrumb['icon'] }} mr-1"></i>@endisset
                    {{ $breadcrumb['label'] }}
                </a>
            @else
                {{ $breadcrumb['label'] }}
            @endif
        </li>
    @endforeach
</ol>
