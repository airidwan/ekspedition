@extends('layouts.login')

@section('title', trans('shared/common.locked-screen'))

@section('content')
<div class="full-content-center">
    <p class="text-center"><h2 style="color: #f5f5f5;"><strong>&nbsp;</strong></h2></p>
    <div class="login-wrap">
        <div class="login-block">
            <?php 
            $foto = !empty(Auth::user()->foto) ? Config::get('app.paths.foto-user').'/'.Auth::user()->foto : 'images/users/user.png'; 
            $fotoUser = file_exists($foto) ? $foto : 'images/users/user.png';
            ?>
            <img src="{{ url($fotoUser) }}" style="box-shadow: none;" class=" not-logged-avatar img-responsive">
            <form role="form" method="post" action="{{ url('/locked/post') }}">
                {{ csrf_field() }}
                <div class="text-center" style=" color:#253f56; margin-bottom:10px;" >
                <strong>{{ strtoupper(Auth::user()->full_name) }}</strong>
                </div>
                <div class="form-group login-input{{ !empty($error) ? ' has-error' : '' }} ">
                    <i class="fa fa-key overlay"></i>
                    <input type="password" id="password" name="password" class="form-control text-input" placeholder="********" autocomplete=off autofocus>
                    <?php
                if (!empty($error))
                    echo "<span class=\"help-block\"><strong>". $error ."</strong></span>";
                 ?>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-success btn-block">UNLOCK</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                       <a class="btn btn-primary btn-block" href="{{ url('/locked/not-user') }}">Not <strong> {{ Auth::user()->full_name }} </strong> ?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script>
$(document).on('ready', function(){
    setTimeout(function(){
       window.location = '{{ URL('/') }}/logout';
    }, 120 * 60 * 1000);
});
</script>
@endsection