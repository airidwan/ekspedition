<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>AR Karyati - @yield('title')</title>
        @section('header')
            @include('includes.header')
        @show
    </head>

    <body class="fixed-left full-content">
        <div class="container">
            <div class="full-content-center">
                @section('content')
                @show

                @section('script')
                    @include('includes.script')
                @show
            </div>
        </div>
    </body>
</html>
