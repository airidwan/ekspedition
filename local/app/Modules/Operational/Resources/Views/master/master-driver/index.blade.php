@extends('layouts.master')

@section('title', trans('operational/menu.driver'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.driver') }}</h2>
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
                                <label for="name" class="col-sm-4 control-label">{{ trans('shared/common.name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ !empty($filters['name']) ? $filters['name'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('position') ? 'has-error' : '' }}">
                                <label for="position" class="col-sm-4 control-label">{{ trans('operational/fields.position') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="position" name="position">
                                        <?php $stringPosition = !empty($filters['position']) ? $filters['position'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionPosition as $position)
                                        <option value="{{ $position->lookup_code }}" {{ $position->lookup_code == $stringPosition ? 'selected' : '' }}>{{ $position->meaning }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('position'))
                                    <span class="help-block">{{ $errors->first('position') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = !empty($filters['type']) ? $filters['type'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
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
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL('operational/master/master-driver/add') }}" class="btn btn-sm btn-primary">
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
                        <table class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('shared/common.code') }}</th>
                                    <th>{{ trans('shared/common.name') }}</th>
                                    <th>{{ trans('operational/fields.nickname') }}</th>
                                    <th>{{ trans('operational/fields.no-ktp') }}</th>
                                    <th>{{ trans('shared/common.alamat') }}</th>
                                    <th>{{ trans('shared/common.kota') }}</th>
                                    <th>{{ trans('shared/common.telepon') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.tanggal-masuk') }}</th>
                                    <th>{{ trans('operational/fields.tanggal-keluar') }}</th>
                                    <th>{{ trans('shared/common.status') }}</th>
                                    <th>{{ trans('shared/common.keterangan') }}</th>
                                    <th>{{ trans('operational/fields.sub-account') }}</th>
                                    <th>{{ trans('shared/common.status') }}</th>
                                    <th width="7%">{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                                @foreach($models as $model)
                                 <?php
                                     $joinDate    = !empty($model->join_date) ? new \DateTime($model->join_date) : null;
                                     $resignDate  = !empty($model->resign_date) ? new \DateTime($model->resign_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->driver_code }}</td>
                                    <td>{{ $model->driver_name }}</td>
                                    <td>{{ $model->driver_nickname }}</td>
                                    <td>{{ $model->identity_number }}</td>
                                    <td>{{ $model->address }}</td>
                                    <td>{{ $model->city_name }}</td>
                                    <td>{{ $model->phone_number }}</td>
                                    <td>{{ $model->driver_category }}</td>
                                    <td>{{ $model->type }}</td>
                                    <td>{{ !empty($joinDate) ? $joinDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ !empty($resignDate) ? $resignDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->merried_status }}</td>
                                    <td>{{ $model->description }}</td>
                                    <td>{{ $model->subaccount_code }}</td>
                                    <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                    </td>
                                    <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' . $model->driver_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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
