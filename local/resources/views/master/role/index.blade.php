@extends('layouts.master')

@section('title', trans('sys-admin/menu.role'))

@section('content')
<div class="row">
    <div class="col-md-12 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-laptop"></i> {{ trans('sys-admin/menu.role') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <form  role="form" id="registerForm" class="form-horizontal" method="post" action="">
					{{ csrf_field() }}
                    <div id="horizontal-form">
						<div class="col-sm-6 portlets">
							<div class="form-group">
								<label for="name" class="col-sm-4 control-label">{{ trans('sys-admin/fields.role') }}</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="name" name="name" value="{{ !empty($filters['name']) ? $filters['name'] : '' }}">
								</div>
							</div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = !empty($filters['status']) || !Session::has('filters') ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
						</div>
						<div class="clearfix"></div>
				    </div>
                    <div class="data-table-toolbar">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="toolbar-btn-action">
                                    <button type="submit" class="btn  btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                    @can('access', [$resource, 'insert'])
                                    <a href="{{ URL('sys-admin/master/role/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="60px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('sys-admin/fields.role') }}</th>
                                <th width="100px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
							<?php $no = ($data->currentPage() - 1) * $data->perPage() + 1; ?>
							@foreach ($data as $item)
                            <tr>
                                <td style="text-align: center;">{{ $no++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td style="text-align: center;">
                                    @can('access', [$resource, 'update'])
									<a href="{{ URL('sys-admin/master/role/edit/' . $item->id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
										<i class="fa fa-pencil"></i>
									</a>
                                    @endcan
                                </td>
                            </tr>
							@endforeach
                        </tbody>
                    </table>
                </div>

                <div class="data-table-toolbar">
					{!! $data->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
