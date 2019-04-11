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

    <body class="fixed-left">
        @section('modal')
        @show
        @include('includes.modal')

        @include('includes.logout')

        <!-- Begin page -->
        <div id="wrapper">
            @section('top-bar')
                @include('includes.top-bar')
            @show

            @section('leftmenu')
                @include('includes.sidebar-menu', ['items'=> $menu_sidebar->roots()])
            @show

            <!-- Start right content -->
            <div class="content-page">
                <div class="content">
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
                    <!-- ============================================================== -->
                    <!-- Start Content here -->
                    <!-- ============================================================== -->
                    @show
                </div>

            @section('footer')
                <!-- @include('includes.footer') -->
            @show

            </div>
            <!-- End right content -->

        </div>

@section('context-menu')
    @include('includes.context-menu')
@show

@section('script')
    @include('includes.script')
    @include('includes.custom-script')
@show
</body>

</html>
