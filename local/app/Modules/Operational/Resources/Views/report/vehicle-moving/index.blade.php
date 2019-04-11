<?php use App\Modules\Operational\Model\Transaction\ManifestHeader; ?>

@extends('layouts.master')

@section('title', trans('operational/menu.vehicle-moving'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.vehicle-moving') }}</h2>
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
                                <label for="truckCode" class="col-sm-4 control-label">{{ trans('operational/fields.truck-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="truckCode" name="truckCode" value="{{ !empty($filters['truckCode']) ? $filters['truckCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.nopol-truck') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
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
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="truckCategory" class="col-sm-4 control-label">{{ trans('operational/fields.truck-category') }}</label>
                                <div class="col-sm-8">
                                    <?php $truckCategoryString = !empty($filters['truckCategory']) ? $filters['truckCategory'] : ''; ?>
                                    <select class="form-control" name="truckCategory">
                                        <option value="">ALL</option>
                                        @foreach($truckCategory as $option)
                                            <option value="{{ $option->lookup_code }}" {{ $truckCategoryString == $option->lookup_code ? 'selected' : '' }}>{{ $option->meaning }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
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
                                <th>{{ trans('operational/fields.truck-code') }}</th>
                                <th>{{ trans('operational/fields.nopol-truck') }}</th>
                                <th>{{ trans('operational/fields.moving-type') }}</th>
                                <th>{{ trans('operational/fields.manifest-number') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.time') }}</th>
                                <th>{{ trans('operational/fields.driver') }}</th>
                                <th>{{ trans('operational/fields.assistant') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach($models as $model)
                            <?php
                                $date = !empty($model->date) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->truck_code }}</td>
                                <td>{{ $model->police_number }}</td>
                                <td>{{ $model->in_out }}</td>
                                <td>{{ $model->manifest_number }}</td>
                                <td>{{ !empty($date) ? $date->format('d-m-Y') : '' }}</td>
                                <td>{{ !empty($date) ? $date->format('H:i') : '' }}</td>
                                <td>{{ $model->driver_name }}</td>
                                <td>{{ $model->assistant_name }}</td>
                                <td>{{ $model->status }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
