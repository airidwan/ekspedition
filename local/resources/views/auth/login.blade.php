@extends('layouts.login')

@section('title', trans('shared/common.login'))

@section('content')
<div class="full-content-center">
    <p class="text-center"><h2 style="color: #f5f5f5;"><strong>&nbsp;</strong></h2></p>
    <div class="login-wrap animated flipInX">
        <div class="login-block">
            <img src="{{('images/ar-karyati.png')}}" class="not-logged-avatar2 img-responsive" style="box-shadow: none;">
            <form method="POST" role="form" action="{{ url('/login') }}">
            {{ csrf_field() }}
                <div class="login-input form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                    <i class="fa fa-user overlay"></i>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control text-input" placeholder="Username" autofocus>
                    @if ($errors->has('name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
                <div class=" login-input form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <i class="fa fa-key overlay"></i>
                    <input  id="password" type="password" name="password" class="form-control text-input" placeholder="********">
                     @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <input type="hidden" id="timezoneOffset" name="timezoneOffset" value="{{ old('timezoneOffset') }}">
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-success btn-block">LOGIN</button>
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
    var time = new Date();
    var timezoneOffset = -time.getTimezoneOffset();
    $('#timezoneOffset').val(timezoneOffset);

    setTimeout(function(){
       location.reload();
    }, 10 * 60 * 1000);
});
</script>
@endsection