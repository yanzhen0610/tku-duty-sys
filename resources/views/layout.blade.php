<!doctype html>
<html>

<head lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- IE -->
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">

    <!-- favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('/favicon.ico') }}">
    <link rel="manifest" href="{{ asset('/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('/safari-pinned-tab.svg') }}" color="#db1921">
    <meta name="msapplication-TileColor" content="#ffc40d">
    <meta name="theme-color" content="#ffffff">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', __('ui.app_name')))</title>
    @includeFirst(['default-fonts-'.Config::get('app.locale'), 'default-fonts-zh'])
    <link rel="stylesheet" href="{{ url(mix('css/bulma.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css"
        integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />
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