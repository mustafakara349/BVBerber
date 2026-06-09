<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'B&V Barber Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/scss/style.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div id="overlay" class="overlay"></div>

    {{-- TOPBAR --}}
    @include('components.topbar')

    {{-- SIDEBAR --}}
    @include('components.sidebar')

    {{-- MAIN CONTENT --}}
    <main id="content" class="content py-10">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')

            <div class="row">
                <div class="col-12">
                    @include('components.footer')
                </div>
            </div>
        </div>
    </main>

    @stack('scripts')
</body>
</html>
