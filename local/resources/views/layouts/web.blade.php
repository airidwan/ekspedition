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
    @yield('modal')
        <div class="container">
            <div class="full-content-center">
                @if(Session::has('successMessage'))
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ Session::get('successMessage') }}
                </div>
                @endif

                @if($errors->has('errorMessage'))
                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ $errors->first('errorMessage') }}
                </div>
                @endif

                @section('content')
                @show

                @section('script')
                    @include('includes.script')
                @show
            </div>
        </div>
    </body>
    @section('script')
        @include('includes.script')
        @include('includes.custom-script')
    @show
</html>
