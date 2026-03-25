<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="Landing page dan dashboard domain assistant">

        @vite('resources/js/app.js')

        @livewireStyles
    </head>
    <body class="bg-body-tertiary">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
