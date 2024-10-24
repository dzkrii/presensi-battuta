<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Presensi Battuta</title>
    {{-- @vite('resources/css/app.css') --}}
    <style>
        #map {
            height: 400px;
        }
    </style>
</head>

<body>
    {{ $slot }}
</body>

</html>
