<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('title')">
    <!-- Title-->
    <title>@yield('title') | {{$getSettings['site_name']}}</title>
    <!-- Favicon-->
    <link rel="shortcut icon" href="{{ asset('/galerias/favicon.ico') }}" type="image/x-icon">

    @yield('pageCSS')
</head>

<body>
    @yield('content')

    @yield('pageMODAL')

    @yield('pageJS')
</body>

</html>