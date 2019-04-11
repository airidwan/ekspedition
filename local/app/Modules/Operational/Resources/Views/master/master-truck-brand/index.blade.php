@extends('layouts.master')

@section('title', trans('operational/menu.truck-brand'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.truck-brand') }}</h2>
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
                                <label for="code" class="col-sm-4 control-label">{{ trans('shared/common.code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="code" name="code" value="{{ !empty($filters['code']) ? $filters['code'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="brandName" class="col-sm-4 control-label">{{ trans('operational/fields.brand-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="brandName" name="brandName" value="{{ !empty($filters['brandName']) ? $filters['brandName'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
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
                                <button type="submit" class="btn btn-sm btn-info">
                                    <i class="fa fa-search"></i> {{ trans('shared/common.filter') }}
                                </button>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url.'/add') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('shared/common.code') }}</th>
                                    <th>{{ trans('operational/fields.brand-name') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('shared/common.active') }}</th>
                                    <th style="min-width:60px" >{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                                @foreach($models as $model)
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->lookup_code }}</td>
                                    <td>{{ $model->meaning }}</td>
                                    <td>{{ $model->description }}</td>
                                    <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                    <td class="text-center">
                                        @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' . $model->id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
