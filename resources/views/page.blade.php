<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>YoPrint Challenge</title>
        @vite('resources/css/app.css')
        @livewireStyles
    </head>
    <body class="antialiased">
        @livewire('file-uploader')

        @vite('resources/js/app.js')
        @livewireScripts
    </body>
</html>
