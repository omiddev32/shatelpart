<!DOCTYPE html>
@php
    $isRtl = \Laravel\Nova\Nova::rtlEnabled();
@endphp
<html 
    lang="{{ $locale = \Laravel\Nova\Nova::resolveUserLocale(request()) }}"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}" 
    class="h-full {{ $isRtl ? 'font-sans' : 'font-sans' }} antialiased"
>
<head>
    <meta name="theme-color" content="#fff">
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width"/>
    <meta name="locale" content="{{ $locale }}"/>
    <meta name="robots" content="noindex">

    @include('partials.meta')

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">

    @if ($styles = \Laravel\Nova\Nova::availableStyles(request()))
    <!-- Tool Styles -->
        @foreach($styles as $asset)
            <link rel="stylesheet" href="{!! $asset->url() !!}">
        @endforeach
    @endif

    <script>
        // if (localStorage.novaTheme === 'dark' || (!('novaTheme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        //     document.documentElement.classList.add('dark')
        // } else {
            document.documentElement.classList.remove('dark')
        // }
    </script>
</head>
<body class="min-w-site text-sm font-medium min-h-full text-gray-500 dark:text-gray-400 bg-content-theme dark:bg-gray-900">
    @inertia

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Build Nova Instance -->
    <script>
        const config = @json(\Laravel\Nova\Nova::jsonVariables(request()));

        window.Nova = createNovaApp(config)
        Nova.countdown()
    </script>

    @if ($scripts = \Laravel\Nova\Nova::availableScripts(request()))
        <!-- Tool Scripts -->
        @foreach ($scripts as $asset)
            <script src="{!! $asset->url() !!}"></script>
        @endforeach
    @endif

    <!-- Start Nova -->
    <script defer>
        Nova.liftOff()
    </script>
</body>
</html>
