<?php
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
?>

@extends('layouts.master')

@section('title', trans('operational/menu.pickup-form'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.pickup-form') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->pickup_form_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="pickupNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" value="{{ $model->pickup_form_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $date = !empty($model->pickup_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_time) :  TimezoneDateConverter::getClientDateTime();
                                    ?>
                                    <div class="form-group">
                                        <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $date = !empty($model->pickup_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_time) :  TimezoneDateConverter::getClientDateTime();
                                    $hoursFormat = $date !== null ? $date->format('H') : -1 ;
                                    $hours = $hoursFormat ;
                                    ?>
                                    <div class="form-group">
                                        <label for="time" class="col-sm-4 control-label">{{ trans('shared/common.time') }}</label>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;" >
                                            <select class="form-control" id="hours" name="hours" disabled>
                                                @for ($i = 0; $i < 24; $i++)
                                                    <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $hours == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-3" style="padding:0px 0px 0px 15px;">
                                            <select class="form-control" id="minute" name="minute" disabled>
                                                <?php $minuteFormat = $date !== null ? $date->format('i') : -1 ; ?>
                                                <?php $minute = $minuteFormat ; ?>
                                                @for ($i = 0; $i < 60; $i++)
                                                    <option value="{{  Penomoran::getStringNomor($i, 2) }}" {{ $minute == $i ? 'selected' : '' }}> {{ Penomoran::getStringNomor($i, 2) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <?php $statusString = $model->status ?>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="status" name="status" disabled>
                                                @foreach($optionStatus as $status)
                                                <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverId    = !empty($modelDriver) ? $modelDriver->driver_id : '' ; 
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck   = $model->truck;
                                        $truckId      = !empty($modelTruck) ? $modelTruck->truck_id : '' ; 
                                        $policeNumber = !empty($modelTruck) ? $modelTruck->police_number : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ $policeNumber }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="noteHeader" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="3" id="noteHeader" name="noteHeader" disabled>{{ $model->note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('marketing/fields.pickup-request-number') }}</th>
                                                    <th>{{ trans('operational/fields.customer') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('shared/common.note') }}</th>
                                                    <th>{{ trans('operational/fields.address') }}</th>
                                                    <th>{{ trans('shared/common.telepon') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}</th>
                                                    <th>{{ trans('operational/fields.dimension') }}</th>
                                                    <th>{{ trans('operational/fields.pickup-cost') }}</th>
                                                    <th>{{ trans('shared/common.note') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->get() as $line)
                                                    <?php
                                                        $request = $line->pickupRequest;
                                                        $resi    = $line->resi;
                                                    ?>
                                                    <tr>
                                                        <td > {{ $request !== null ? $request->pickup_request_number : '' }} </td>
                                                        <td > {{ $request !== null ? $request->customer_name : '' }} </td>
                                                        <td > {{ $request !== null ? $request->item_name : '' }} </td>
                                                        <td > {{ $request !== null ? $request->note : '' }} </td>
                                                        <td > {{ $request !== null ? $request->address : '' }} </td>
                                                        <td > {{ $request !== null ? $request->phone_number : '' }} </td>
                                                        <td class="text-right"> {{ $request !== null ? number_format($request->total_coly) : '' }} </td>
                                                        <td class="text-right"> {{ $request !== null ? number_format($request->weight, 2) : '' }} </td>
                                                        <td class="text-right"> {{ $request !== null ? number_format($request->dimension, 6) : '' }} </td>
                                                        <td class="text-right"> {{ $request !== null ? number_format($request->pickup_cost) : '' }} </td>
                                                        <td > {{ $request !== null ? $request->note_add : '' }} </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection
