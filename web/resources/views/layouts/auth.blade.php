<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Giriş - B&V Barber')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.35.0/dist/tabler-icons.min.css">
    @vite(['resources/scss/style.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-auth">
    <div id="skeletonGlobalProgress" aria-hidden="true"></div>
    @yield('content')
    @stack('scripts')
</body>
</html>
