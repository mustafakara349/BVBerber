<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Giriş - B&V Barber')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/scss/style.scss', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
