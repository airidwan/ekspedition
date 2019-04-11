@extends('layouts.master')

@section('title', trans('sys-admin/menu.role'))

@section('header')
@parent
<link href="{{ asset('assets/libs/jquery-nestable/jquery.nestable.css') }}" rel="stylesheet" type="text/css" />
<style media="screen">
    .privileges{
        background-color: transparent;
    }
</style>
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="widget">
			<div class="widget-header">
                <h2><i class="fa fa-laptop"></i> <strong>{{ $title }}</strong> {{ trans('sys-admin/menu.role') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
			</div>
			<div class="widget-content padding">
				<div id="horizontal-form">
					<form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url('sys-admin/master/role/save') }}">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ $model->id }}">
						<div class="col-sm-6 portlets">
							<div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
								<label for="name" class="col-sm-4 control-label">{{ trans('sys-admin/fields.role') }} <span class="required">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->name }}">
        							@if ($errors->has('name'))
                                    <span class="help-block">{{ $errors->first('name') }}</span>
                                    @endif
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
						<div class="clearfix"></div>
						<hr>
						<div class="col-sm-12 portlets">
                            <div class="dd" id="nestable">
                                <ol class="dd-list">
                                    @foreach($resources as $module => $moduleResources)
                                    <li class="dd-item">
                                        <div class="dd-handle">{{ ucfirst($module) }}</div>
                                        @foreach($moduleResources as $subModule => $subModuleResources)
                                        <ol class="dd-list">
                                            <li class="dd-item">
                                                <div class="dd-handle">{{ ucfirst($subModule) }}</div>
                                                @foreach($subModuleResources as $resource => $privileges)
                                                <ol class="dd-list">
                                                    <li class="dd-item">
                                                        <div class="dd-handle">{{ $resource }}</div>
                                                        <ol class="dd-list">
                                                            <li class="dd-item" data-id="3">
                                                                <div class="dd-handle privileges">
                                                                    <input type="checkbox" name="all-privileges[]" value="{{ $resource }}" class="all-privileges">All
                                                                </div>
                                                            </li>
                                                            @foreach($privileges as $privilege)
                                                            <?php
                                                            $access = !empty(old('privileges')) ? !empty(old('privileges')[$resource][$privilege]) : $model->canAccess($resource, $privilege);
                                                            ?>
                                                            <li class="dd-item" data-id="3">
                                                                <div class="dd-handle privileges">
                                                                    <input type="checkbox" name="privileges[{{ $resource }}][{{ $privilege }}]" value="1" {{ $access ? 'checked' : '' }}>{{ $privilege }}
                                                                </div>
                                                            </li>
                                                            @endforeach
                                                        </ol>
                                                    </li>
                                                </ol>
                                                @endforeach
                                            </li>
                                        </ol>
                                        @endforeach
                                    </li>
                                    @endforeach
                                </ol>
                            </div>
						</div>
						<div class="clearfix"></div>
						<hr>
						<div class="col-sm-offset-3 col-sm-9 portlets">
							<div class="form-group text-right">
								<a href="{{ url('sys-admin/master/role') }}" class="btn btn-warning"> <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
								<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
							</div>
                            <br>
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
<script src="{{ asset('assets/libs/jquery-nestable/jquery.nestable.js') }}"></script>
<script type="text/javascript">
	$(document).on('ready', function() {
        $('#nestable').nestable().nestable('collapseAll');
        $('.all-privileges').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().find('input');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
	});
</script>
@endsection
