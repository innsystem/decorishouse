<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="utf-8" />
    <title>InnSystem Dashboard</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('/favicon.ico?1') }}" type="image/x-icon">

    <!-- Theme Config Js -->
    <script src="{{ asset('/tpl_dashboard/js/config.js?2') }}"></script>

    <!-- App css -->
    <link href="{{ asset('/tpl_dashboard/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <!-- App css -->
    <link href="{{ asset('/tpl_dashboard/css/custom_template.css') }}" rel="stylesheet" type="text/css" />

    <!-- Icons css -->
    <link href="{{ asset('/tpl_dashboard/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Font Awesome -->
    <link href="{{ asset('/plugins/fontawesome/css/all.min.css') }}" rel="stylesheet">

    <!-- SwalFire -->
    <link href="{{ asset('/plugins/sweetalert/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            background-color: #2d2d2d;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background-color: #373737;
            border-radius: 15px;
            padding: 30px;
            color: #fff;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
        }

        .brand-logo {
            display: block;
            margin: 0 auto 20px;
        }

        .form-label {
            font-size: 14px;
            color: #B0B3C5;
        }

        .form-control {
            background-color: #2d2d2d;
            color: #fff;
            border: 1px solid #B0B3C5;
            border-radius: 5px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #37adef;
        }

        .btn-primary {
            background-color: #37adef;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: bold;
            color: #2d2d2d;
        }

        .btn-primary:hover {
            background-color: rgb(36, 155, 224);
        }

        .forgot-password,
        .terms {
            color: #B0B3C5;
            font-size: 12px;
            text-decoration: none;
        }

        .forgot-password:hover,
        .terms:hover {
            color: #37adef;
        }

        .password-strength {
            font-size: 12px;
            color: #37adef;
            margin-top: 5px;
        }
    </style>

    @yield('pageCSS')
</head>

<!-- body start -->

<body>
    <div class="login-container">
        <img src="{{ asset('/innsystem-logo-light.png') }}" alt="InnSystem Dashboard" class="brand-logo">
        <h4 class="text-center mb-4">Faça seu Acesso</h4>
        @yield('content')
        <div class="text-center fs-7 mt-4">
            <script>
                document.write(new Date().getFullYear())
            </script> © Desenvolvido por <a href="https://innsystem.com.br" target="_Blank" class="fw-bold">InnSystem Inovação em Sistemas</a>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="{{ asset('/tpl_dashboard/js/vendor.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('/tpl_dashboard/js/app.min.js') }}"></script>

    <script src="{{ asset('/plugins/sweetalert/sweetalert2.min.js') }}"></script>


    <script src="{{ asset('/plugins/jquery.mask.js') }}"></script>
    <script src="{{ asset('/plugins/jquery.mask.templates.js') }}"></script>



    @yield('pageJS')

</body>

</html>