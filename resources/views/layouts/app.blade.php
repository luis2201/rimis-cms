<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RIMIS') }} | Administración</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=source-sans-pro:300,400,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    </head>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
        <div class="wrapper">
            @include('layouts.partials.navbar')
            @include('layouts.partials.sidebar')

            <div class="content-wrapper">
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-7">
                                @if (isset($header))
                                    {{ $header }}
                                @endif
                            </div>
                            <div class="col-sm-5">
                                @include('layouts.partials.breadcrumbs')
                            </div>
                        </div>
                    </div>
                </section>

                <section class="content pb-4">
                    <div class="container-fluid">
                        @include('layouts.partials.alerts')
                        {{ $slot }}
                    </div>
                </section>
            </div>

            @include('layouts.partials.footer')
        </div>
    </body>
</html>
