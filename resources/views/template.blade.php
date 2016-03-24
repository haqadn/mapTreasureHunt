<!DOCTYPE html>
<html>
    <head>
        <title>@yield( 'title' ) - {{ config('app.title') }}</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <link href="asset/css/app.css" rel="stylesheet" type="text/css">
        
        @section('scripts')
        <!-- Bootstrap JS -->
        <script src="asset/js/bootstrap.min.js"></script>
        @show

    </head>
    <body>
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-8" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{{ config('app.url') }}">{{ config('app.title') }}</a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-8">
                    <ul class="nav navbar-nav">
                        <li class="{{active_class('home')}}"><a href="{{ route('home') }}">{{ trans('pages.home') }}</a></li>
                        <li class="{{active_class('game')}}"><a href="{{ route('home') }}">{{ trans('pages.game') }}</a></li>
                        <li class="{{active_class('ranklist')}}"><a href="{{ route('home') }}">{{ trans( 'pages.ranklist' ) }}</a></li>
                        <li class="{{active_class('help')}}"><a href="{{ route('help') }}">{{ trans( 'pages.help' ) }}</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="content">
        @yield( 'content' )
        </div>
    </body>
</html>
