@extends('layouts.master')

@section('title', trans('inventory/menu.master-warehouse'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> {{ trans('inventory/menu.master-warehouse') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="whCode" class="col-sm-4 control-label">{{ trans('inventory/fields.warehouse-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="whCode" name="whCode" value="{{ !empty($filters['whCode']) ? $filters['whCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.deskripsi') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
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
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.warehouse-code') }}</th>
                                <th>{{ trans('shared/common.deskripsi') }}</th>
                                <th>{{ trans('shared/common.cabang') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->wh_code }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->branch_name }}</td>
                                <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->wh_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection