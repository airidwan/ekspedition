@extends('layouts.master')

@section('title', 'Change Profile')

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="widget">
			<div class="widget-header">
                <h2><i class="fa fa-laptop"></i> <strong>Change</strong> Profile</h2>
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
    							<div class="form-group {{ $errors->has('fullName') ? 'has-error' : '' }}">
                                    <label for="fullName" class="col-sm-4 control-label">{{ trans('sys-admin/fields.name') }} <span class="required">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="fullName" name="fullName" value="{{ count($errors) > 0 ? old('fullName') : $model->full_name }}">
                                        @if ($errors->has('fullName'))
                                        <span class="help-block">{{ $errors->first('fullName') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="col-sm-4 control-label">{{ trans('sys-admin/fields.username') }} <span class="required">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->name }}">
                                        @if ($errors->has('name'))
                                        <span class="help-block">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label for="email" class="col-sm-4 control-label">{{ trans('sys-admin/fields.email') }} <span class="required">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="email" name="email" value="{{ count($errors) > 0 ? old('email') : $model->email }}">
                                        @if ($errors->has('email'))
                                        <span class="help-block">{{ $errors->first('email') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{ trans('sys-admin/fields.foto') }}</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="foto" name="foto" style="display:none">
                                        <div class="btn btn-photo well text-center" style="padding: 5px; margin: 0px;">
                                            @if(!empty($model->foto))
                                            <img height="150" src="{{ asset(Config::get('app.paths.foto-user').'/'.$model->foto) }}"/><span></span>
                                            @else
                                            <img height="150" hidden/><span>{{ trans('shared/common.choose-file') }}</span>
                                            @endif
                                        </div>
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
});
</script>
@endsection
