<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Enjoy - Chat') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="container">
    <div class="d-flex align-content-center justify-content-center flex-column flex-wrap vh-100">
        <h1>{{ config('app.name') }}</h1>
        <span>sharing incredible things</span>
    </div>
</body>
</html>
