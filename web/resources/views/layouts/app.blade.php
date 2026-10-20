<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'B&V Barber Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.35.0/dist/tabler-icons.min.css">
    @vite(['resources/scss/style.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div id="skeletonGlobalProgress" aria-hidden="true"></div>
    <div id="overlay" class="overlay"></div>

    {{-- TOPBAR --}}
    @include('components.topbar')

    {{-- SIDEBAR --}}
    @include('components.sidebar')

    {{-- MAIN CONTENT --}}
    <main id="content" class="content py-10 skeleton-content-fade">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show page-animate-fade-up mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show page-animate-fade-up mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show page-animate-fade-up mb-4" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="page-body-animate">
                @yield('content')
            </div>

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
