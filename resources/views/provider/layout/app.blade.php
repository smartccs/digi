<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ Setting::get('site_favicon', asset('favicon.ico')) }}" type="image/x-icon">
    <link rel="icon" href="{{ Setting::get('site_favicon', asset('favicon.ico')) }}" type="image/x-icon">

    <title>{{ config('app.name', 'Tranxit') }}</title>

    <!-- Styles -->
    <link href="{{ asset('asset/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('asset/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/slick.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/slick-theme.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/rating.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset/css/dashboard-style.css') }}" rel="stylesheet" type="text/css">
    @yield('styles')

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>
<body>

    @include('provider.layout.partials.header')

    <div class="page-content dashboard-page">
        <div class="container">
            @include('provider.layout.partials.left')
            @yield('content')
        </div>
    </div>

    <div class="row footer no-margin">
        <div class="container">
            <div class="col-md-6 text-left">
                <p>{{ Setting::get('site_copyright', '&copy; 2017 Appoets') }}</p>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ Setting::get('app_url_ios', '#') }}" class="app"><img src="/asset/img/appstore.png"></a>
                <a href="{{ Setting::get('app_url_android', '#') }}" class="app"><img src="/asset/img/playstore.png"></a>
            </div>
        </div>
    </div>

    @include('provider.layout.partials.modal')

    <!-- Scripts -->
    <script type="text/javascript" src="{{ asset('asset/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/jquery.mousewheel.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/jquery-migrate-1.2.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/slick.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/rating.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/js/incoming.js') }}"></script>
    <script type="text/javascript">
        $.incoming({
            'url': '{{ route('api') }}',
            'modal': 'modal-incoming'
        })
    </script>
    @yield('scripts')

</body>
</html>