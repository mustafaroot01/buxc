<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'نظام الحضور والغياب') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">


        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#0d9488">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/pwa-icon.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="الحضور">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="نظام الحضور">
        <meta name="msapplication-TileColor" content="#0d9488">
        <meta name="msapplication-tap-highlight" content="no">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
