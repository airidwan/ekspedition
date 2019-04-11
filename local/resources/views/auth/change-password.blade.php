@extends('layouts.master')

@section('title', 'Change Password')

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="widget">
			<div class="widget-header">
                <h2><i class="fa fa-laptop"></i> <strong>Change</strong> Password</h2>
                <div class="additional-btn">
                    <a href="#" class="reload"><i class="icon-ccw-1"></i></a>
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-close"><i class="icon-cancel-3"></i></a>
                </div>
			</div>
			<div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="" enctype="multipart/form-data">
                        {{ csrf_field() }}
    						<div class="col-sm-8 portlets">
    							<div class="form-group">
    								<label for="name" class="col-sm-4 control-label">Username</label>
    								<div class="col-sm-6">
    									<input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" disabled="disabled">
    								</div>
    							</div>
    							<div class="form-group">
    								<label for="email" class="col-sm-4 control-label">Email</label>
    								<div class="col-sm-6">
    									<input type="text" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" disabled="disabled">
    								</div>
    							</div>
                                <div class="form-group{{ $errors->has('oldPassword') ? ' has-error' : '' }}">
                                    <label for="oldPassword" class="col-md-4 control-label">Old Password <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input id="oldPassword" type="password" class="form-control" name="oldPassword" value="{{ old('oldPassword') }}">
                                        @if ($errors->has('oldPassword'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('oldPassword') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label for="password" class="col-md-4 control-label">New Password <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" value="{{ old('password') }}">
                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                    <label for="password-confirm" class="col-md-4 control-label">Confirm New Password <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}">
                                        @if ($errors->has('password_confirmation'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
    						</div>
        				<div class="clearfix"></div>
        				<hr>
        				<div class="col-sm-12 data-table-toolbar text-right">
        					<div class="form-group">
        						<a href="{{ url('/') }}" class="btn btn-sm btn-warning">Cancel</a>
        						<button type="submit" class="btn btn-sm btn-primary">Simpan</button>
        					</div>
        				</div>
                    </form>
                </div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function() {
    $('.btn-photo').on('click', function(){
        $(this).parent().find('input[type="file"]').click();
    });

    $("#foto").on('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            var $img    = $(this).parent().find('img');
            var $span   = $(this).parent().find('span');
            reader.onload = function (e) {
                $img.attr('src', e.target.result);
                $img.show();
                $span.hide();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

	$('.delete-role').on('click', function() {
		$(this).parent().parent().remove();
	});

	$('#add-role').on('click', function() {
		$('#table-role tbody').append(
			'<tr>' +
			'<td >' + $('#role option:selected').text() + '</td>' +
			'<td class="text-center">' +
			'<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-role" ><i class="fa fa-remove"></i></a>' +
			'<input type="hidden" name="roles[]" value="' + $('#role').val() + '">' +
			'</td>' +
			'</tr>'
			);

		$('.delete-role').on('click', function() {
			$(this).parent().parent().remove();
		});
	});
});
</script>
@endsection
