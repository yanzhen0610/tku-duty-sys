<!doctype html>
<html>

<head lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    @includeFirst(['default-fonts-'.Config::get('app.locale'), 'default-fonts-zh'])
    <link rel="stylesheet" href="{{ mix('css/bulma.css') }}">
    <link rel="stylesheet" href="{{ mix('css/materialize/checkboxes.css') }}">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    @stack('headers')
</head>

<body>
    @include('navbar')
    <section class="section">
        <div class="container">
            @yield('content')
        </div>
    </section>
</body>

</html>