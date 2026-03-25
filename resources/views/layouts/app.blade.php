<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="Landing page dan dashboard domain assistant">

        @if (file_exists(public_path('build/manifest.json')))
            @vite('resources/js/app.js')
        @endif

        @livewireStyles
    </head>
    <body class="bg-body-tertiary">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
