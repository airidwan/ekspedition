@extends('layouts.master')

@section('title', trans('operational/menu.approve-delivery-order'))
<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.approve-delivery-order') }}</h2>
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
                                <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ !empty($filters['deliveryOrderNumber']) ? $filters['deliveryOrderNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driver" name="driver" value="{{ !empty($filters['driver']) ? $filters['driver'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" style="text-transform: uppercase" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="partnerName" class="col-sm-4 control-label">{{ trans('operational/fields.partner-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="partnerName" name="partnerName" value="{{ !empty($filters['partnerName']) ? $filters['partnerName'] : '' }}">
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
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
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.do-number') }}</th>
                                <th>{{ trans('operational/fields.driver') }}</th>
                                <th>{{ trans('operational/fields.driver-assistant') }}</th>
                                <th>{{ trans('operational/fields.police-number') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.start-time') }}</th>
                                <th>{{ trans('shared/common.end-time') }}</th>
                                 <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('operational/fields.partner-name') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $startTime      = !empty($model->delivery_start_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->delivery_start_time) : null;
                                 $endTime      =!empty($model->delivery_end_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->delivery_end_time) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->delivery_order_number }}</td>
                                <td>{{ $model->driver_name }}</td>
                                <td>{{ $model->assistant_name }}</td>
                                <td>{{ $model->police_number }}</td>
                                <td>{{ !empty($startTime) ? $startTime->format('d-M-Y') : '' }}</td>
                                <td>{{ !empty($startTime) ? $startTime->format('H:i') : '' }}</td>
                                <td>{{ !empty($endTime) ? $endTime->format('H:i') : '' }}</td>
                                <td>{{ $model->type }}</td>
                                <td>{{ $model->vendor_name }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->delivery_order_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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