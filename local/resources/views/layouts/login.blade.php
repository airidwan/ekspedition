<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="developer_name" content="Faisol Andi Sefihara"/>
        <meta name="developer_website" content="faisolhara.com"/>
        <meta name="developer_email" content="sfaisolandi@gmail.com"/>
        <title>AR Karyati - @yield('title')</title>
        @section('header')
            @include('includes.header')
        @show
    </head>

    <body class="fixed-left login-page">
        <div class="container">
            @section('content')
            @show
        </div>
        @section('script')
            @include('includes.script')
        @show
    </body>
</html>
