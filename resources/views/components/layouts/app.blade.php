<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Crypto Price Tracker</title>

    
    <meta name="reverb-key" content="{{ config('reverb.app_key') }}">
    <meta name="reverb-host" content="{{ config('reverb.host', '127.0.0.1') }}">
    <meta name="reverb-port" content="{{ config('reverb.port', '8080') }}">
    
    @livewireStyles
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    {{ $slot }}
    
    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            window.Echo.channel('prices')
                .listen('.price.updated', (data) => {
                    Livewire.dispatch('price-updated', data);
                });
        });
    </script>
</body>
</html>