@extends('layouts.master')

@section('title', trans('sys-admin/menu.user'))

@section('header')
@parent
<style type="text/css">
    .table tr.role td { back: bold; background-color: #eee; }
    .table tr.role td:first-child { font-weight: bold; }
    .table tr.branch td:first-child { padding-left: 30px; }
</style>
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="widget">
			<div class="widget-header">
                <h2><i class="fa fa-laptop"></i> <strong>{{ $title }} </strong>  {{ trans('sys-admin/menu.user') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
			</div>
			<div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url('sys-admin/master/user/save') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <ul id="demo1" class="nav nav-tabs">
        					<li class="active">
        						<a href="#tabMasterUser" data-toggle="tab">{{ trans('sys-admin/menu.user') }} <span class="label label-success"></span></a>
        					</li>
        					<li class="">
        						<a href="#tabAktifasi" data-toggle="tab">{{ trans('sys-admin/fields.role') }} <span class="badge badge-primary"></span></a>
        					</li>
        				</ul>
        				<div class="tab-content">
        					<div class="tab-pane fade active in" id="tabMasterUser">
        						<input type="hidden" name="id" value="{{ $model->id }}">
        						<div class="col-sm-6 portlets">
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
        							<div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
        								<label for="password" class="col-sm-4 control-label">
                                            {{ trans('sys-admin/fields.password') }}
                                            @if(empty($model->id))
                                                <span class="required">*</span>
                                            @endif
                                        </label>
        								<div class="col-sm-8">
        									<input type="password" class="form-control" id="password" name="password" value="{{ count($errors) > 0 ? old('password') : '' }}">
                                            @if ($errors->has('password'))
                                            <span class="help-block">{{ $errors->first('password') }}</span>
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
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                                <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                            </label>
                                        </div>
                                    </div>
        						</div>
                            </div>
                            <div class="tab-pane fade" id="tabAktifasi">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="email" class="col-sm-4 control-label">{{ trans('sys-admin/fields.role') }} <span class="required">*</span></label>
                                        <div class="col-sm-6">
                                            <select class="form-control" id="role" name="role">
                                                @foreach($roleOptions as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a class="btn btn-sm btn-primary" id="add-role">{{ trans('shared/common.add') }} {{ trans('sys-admin/fields.role') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" cellspacing="0" id="table-role">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('sys-admin/fields.role') }} / {{ trans('shared/common.cabang') }}</th>
                                                    <th width="100px">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(count($errors) > 0):
                                                @foreach(old('roleBranch', []) as $roleId => $branchIds)
                                                <?php
                                                $role = App\Role::find(intval($roleId));
                                                ?>
                                                <tr class="role">
                                                    <td>{{ $role->name }}</td>
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-role" data-role="{{ $role->id }}"><i class="fa fa-remove"></i></a>
                                                    </td>
                                                </tr>
                                                @foreach($cabangOptions as $branch)
                                                <tr class="branch branch-{{ $role->id }}">
                                                    <td>{{ $branch->branch_name }}</td>
                                                    <td class="text-center">
                                                        <input name="roleBranch[{{ $role->id }}][]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check" {{ in_array($branch->branch_id, $branchIds) ? 'checked' : '' }}>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endforeach

                                                @else
                                                @foreach($model->userRole()->get() as $userRole)
                                                <?php
                                                $role = $userRole->role()->first();
                                                if ($role === null || $role->active == 'N') {
                                                    continue;
                                                }
                                                ?>
                                                <tr class="role">
                                                    <td>{{ $role->name }}</td>
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-role" data-role="{{ $role->id }}"><i class="fa fa-remove"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                                $branchIds = [];
                                                foreach($userRole->userRoleBranch()->get() as $userRoleBranch) {
                                                    $branch = $userRoleBranch->branch()->first();
                                                    if ($branch !== null && $branch->active == 'Y') {
                                                        $branchIds[] = $branch->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($cabangOptions as $branch)
                                                <tr class="branch branch-{{ $role->id }}">
                                                    <td>{{ $branch->branch_name }}</td>
                                                    <td class="text-center">
                                                        <input name="roleBranch[{{ $role->id }}][]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check" {{ in_array($branch->branch_id, $branchIds) ? 'checked' : '' }}>
                                                    </td>
                                                </tr>
                                                @endforeach

                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
        				<div class="clearfix"></div>
        				<hr>
        				<div class="col-sm-12 data-table-toolbar text-right">
        					<div class="form-group">
        						<a href="{{ url('sys-admin/master/user') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
        						<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
    $('#all-cabang').on('ifChanged', function(){
        var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
        if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
            $inputs.iCheck('check');
        } else {
            $inputs.iCheck('uncheck');
        }
    });

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
        var roleId = $(this).data('role');
        $(this).parent().parent().remove();
        $('.branch-' + roleId).remove();
	});

	$('#add-role').on('click', function() {
        var roleId = $('#role').val();
		$('#table-role tbody').append(
			'<tr class="role">' +
			'<td >' + $('#role option:selected').text() + '</td>' +
			'<td class="text-center">' +
			'<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-role" data-role="'+ roleId +'"><i class="fa fa-remove"></i></a>' +
			'</td>' +
			'</tr>'
		);

        var trBranch = '';
        @foreach($cabangOptions as $branch)
        trBranch += '<tr class="branch branch-' + roleId + '">' +
            '<td>{{ $branch->branch_name }}</td>' +
            '<td class="text-center">' +
                '<input name="roleBranch['+ roleId + '][]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check">' +
            '</td>' +
        '</tr>';
        @endforeach;

        $('#table-role tbody').append(trBranch);

        $('input:not(.ios-switch)').iCheck({
          checkboxClass: 'icheckbox_square-aero',
          radioClass: 'iradio_square-aero',
          increaseArea: '20%' // optional
        });

		$('.delete-role').on('click', function() {
            var roleId = $(this).data('role');
			$(this).parent().parent().remove();
            $('.branch-' + roleId).remove();
		});
	});
});
</script>
@endsection
