<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Favicon same as other pages -->
    <link rel="icon" type="image/png" href="{{ asset('images/logoweb.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: url('{{ asset('images/KILANGBACK.jpg') }}') no-repeat center center fixed;
            background-size: cover;
        }
        .bg-overlay {
            background: rgba(255,255,255,0.15); /* Lebih transparan */
            border-radius: 0;
            box-shadow: none;
            width: 100vw;
            min-height: 100vh;
            min-width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        @media (max-width: 640px) {
            .bg-overlay {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center">
    <div class="bg-overlay">
        {{ $slot }}
    </div>
</body>
</html>