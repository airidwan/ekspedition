<?php
use App\Service\Penomoran;
use App\Service\TimezoneDateConverter;
?>

@extends('layouts.master')

@section('title', trans('marketing/menu.pickup-request'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> <strong>{{ $title }}</strong> {{ trans('marketing/menu.pickup-request') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->pickup_request_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-request-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" value="{{ $model->pickup_request_number }}" disabled>
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->pickup_request_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_request_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            ?>
                            <div class="form-group">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" disabled>
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : TimezoneDateConverter::getClientDateTime();
                            } else {
                                $date = !empty($model->pickup_request_time) ? TimezoneDateConverter::getClientDateTime($model->pickup_request_time) :  TimezoneDateConverter::getClientDateTime();
                            }
                            $hoursFormat = $date !== null ? $date->format('H') : -1 ;
                            $hours = $hoursFormat ;
                            ?>
                            <div class="form-group">
                                <label for="time" class="col-sm-4 control-label">{{ trans('shared/common.time') }}</label>
                                <div class="col-sm-3" style="padding:0px 0px 0px 15px;" disabled>
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
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status" disabled>
                                        <?php $statusString = $model->status ?>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ $statusString == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="callersName" class="col-sm-4 control-label">{{ trans('marketing/fields.callers-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="callersName" name="callersName" value="{{ $model->callers_name }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $model->customer_name }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="address" name="address" value="{{ $model->address }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ $model->phone_number }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemName" name="itemName" value="{{ $model->item_name }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="totalColy" name="totalColy" value="{{ $model->total_coly }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} (Kg)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal" id="weight" name="weight" value="{{ $model->weight }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="volume" class="col-sm-4 control-label">{{ trans('operational/fields.dimension') }} (Cm)</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionL" placeholder="L" name="dimensionL" value="{{ $model->dimension_long }}" disabled>
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionW" placeholder="W" name="dimensionW" value="{{ $model->dimension_width }}" disabled>
                                </div>    
                                <div class="col-sm-2">
                                    <input type="text" class="form-control decimal text-right" id="dimensionH" placeholder="H" name="dimensionH" value="{{ $model->dimension_height }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dimension" class="col-sm-4 control-label">{{ trans('operational/fields.volume') }} (M<sup>3</sup>)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal text-right" id="dimension" name="dimension" value="{{ $model->dimension }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="3"  maxlength="255" disabled>{{ $model->note }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pickupCost" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-cost') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="pickupCost" name="pickupCost" value="{{ $model->pickup_cost }}" disabled>
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
