<?php use App\Modules\Operational\Model\Transaction\ManifestHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.manifest-arrived'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.manifest-arrived') }}</h2>
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
                                <label for="manifestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" value="{{ !empty($filters['manifestNumber']) ? $filters['manifestNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driver" name="driver" value="{{ !empty($filters['driver']) ? $filters['driver'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driverAssistant" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driverAssistant" name="driverAssistant" value="{{ !empty($filters['driverAssistant']) ? $filters['driverAssistant'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nopolTruck" class="col-sm-4 control-label">{{ trans('operational/fields.nopol-truck') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nopolTruck" name="nopolTruck" value="{{ !empty($filters['nopolTruck']) ? $filters['nopolTruck'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <?php $status = !empty($filters['status']) ? $filters['status'] : ''; ?>
                                    <select class="form-control" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $option)
                                            <option value="{{ $option }}" {{ $status == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('operational/fields.manifest-number') }}<hr/>
                                    {{ trans('operational/fields.date') }}
                                </th>
                                <th>{{ trans('operational/fields.arrived-date') }}</th>
                                <th>{{ trans('operational/fields.route') }}</th>
                                <th>
                                    {{ trans('operational/fields.nopol-truck') }}<hr/>
                                    {{ trans('operational/fields.truck-owner') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.driver') }}<hr/>
                                    {{ trans('operational/fields.driver-assistant') }}
                                </th>
                                <th>{{ trans('operational/fields.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = ManifestHeader::find($model->manifest_header_id);
                            $date = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            $arrivedDate = !empty($model->arrive_date) ? new \DateTime($model->arrive_date) : null;
                            $route = $model->route;
                            $startCity = $route !== null ? $route->cityStart : null;
                            $endCity = $route !== null ? $route->cityEnd : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->manifest_number }}<hr/>
                                    {{ $date !== null ? $date->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $arrivedDate !== null ? $arrivedDate->format('d-m-Y') : '' }}</td>
                                <td>{{ $route !== null ? $route->route_code : '' }}</td>
                                <td>
                                    {{ $model->truck !== null ? $model->truck->police_number : '' }}<hr/>
                                    {{ $model->truck !== null ? $model->truck->owner_name : '' }}
                                </td>
                                <td>
                                    {{ $model->driver !== null ? $model->driver->driver_name : '' }}<hr/>
                                    {{ $model->driverAssistant !== null ? $model->driverAssistant->driver_name : '' }}
                                </td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->manifest_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
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