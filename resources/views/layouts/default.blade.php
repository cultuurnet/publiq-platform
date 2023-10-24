<!doctype html>
<html lang="en">
    <head>
        @include('layouts.gtag')
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @vite([
            'resources/css/app.css',
            'resources/ts/app.tsx',
        ])
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>publiq-platform</title>
        @inertiaHead
        @include('layouts.hotjar')
    </head>
    <body>
        @inertia
    </body>
</html>
